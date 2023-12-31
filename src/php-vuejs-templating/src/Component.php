<?php

namespace WMDE\VueJsTemplating;

use DOMAttr;
use DOMCharacterData;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMText;
use Exception;
use LibXMLError;
use phuety\compiler;
use WMDE\VueJsTemplating\JsParsing\BasicJsExpressionParser;
use WMDE\VueJsTemplating\JsParsing\CachingExpressionParser;
use WMDE\VueJsTemplating\JsParsing\JsExpressionParser;

class Component {

	/**
	 * @var string HTML
	 */
	private $template;

	/**
	 * @var JsExpressionParser
	 */
	private $expressionParser;

	/**
	 * @param string $template HTML
	 * @param callable[] $methods
	 */
	public function __construct($template, array $methods) {
		$this->template = $template;
		$this->expressionParser = new CachingExpressionParser(new BasicJsExpressionParser($methods));
	}

	public function render_page_dom($dom, array $data, array $methods = []) {
		// $dom->is_page = true;
		$this->handleNode($dom->documentElement, $data, $methods);
		// return $dom;
	}
	/**
	 * @param array $data
	 *
	 * @return string HTML
	 */
	public function render_dom($dom, array $data, array $methods = []) {
		#var_dump("render-dom");
		#var_dump($data);
		#var_dump($methods);
		$this->handleNode($dom->documentElement, $data, $methods);
		return;
		$document = $this->parseHtml($this->template);
		$html = [];
		// $rootNodes = $this->getRootNode($document);
		compiler::d("parse fragment", $document->documentElement->childNodes->item(0)->childNodes);
		$rootNode = $document->documentElement->childNodes; // ->item(0)->childNodes;
		foreach ($rootNodes as $root) {
			$this->handleNode($root, $data, $methods);
			$html[] = $document->saveHTML($root);
		}
		return join("\n", $html);
		return $document->saveHTML();
		// return $document->saveHTML($rootNode);
	}

	public function render_page(array $data, array $methods = []) {
		$dom = new DOMDocument();
		@$dom->loadHTML($this->template);
		// $dom->is_page = true;
		$this->handleNode($dom->documentElement, $data, $methods);
		return $dom->saveHTML();
	}
	/**
	 * @param array $data
	 *
	 * @return string HTML
	 */
	public function render(array $data, array $methods = []) {
		#var_dump($data);
		#var_dump($methods);
		$document = $this->parseHtml($this->template);
		$html = [];
		// $rootNodes = $this->getRootNode($document);
		compiler::d("parse fragment", $document->documentElement->childNodes->item(0)->childNodes);
		$rootNode = $document->documentElement->childNodes; // ->item(0)->childNodes;
		foreach ($rootNodes as $root) {
			$this->handleNode($root, $data, $methods);
			$html[] = $document->saveHTML($root);
		}
		return join("\n", $html);
		return $document->saveHTML();
		// return $document->saveHTML($rootNode);
	}

	/**
	 * @param string $html HTML
	 *
	 * @return DOMDocument
	 */
	private function parseHtml($html) {
		if (LIBXML_VERSION < 20900) {
			$entityLoaderDisabled = libxml_disable_entity_loader(true);
		}
		$internalErrors = libxml_use_internal_errors(true);
		$document = new DOMDocument('1.0', 'UTF-8');

		// Ensure $html is treated as UTF-8, see https://stackoverflow.com/a/8218649
		// LIBXML_NOBLANKS Constant excludes "ghost nodes" to avoid violating
		// vue's single root node constraint
		if (!$document->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOBLANKS)) {
			//TODO Test failure
		}

		/** @var LibXMLError[] $errors */
		$errors = libxml_get_errors();
		libxml_clear_errors();

		// Restore previous state
		libxml_use_internal_errors($internalErrors);
		if (LIBXML_VERSION < 20900) {
			libxml_disable_entity_loader($entityLoaderDisabled);
		}

		foreach ($errors as $error) {
			//TODO html5 tags can fail parsing
			//TODO Throw an exception
		}

		return $document;
	}

	/**
	 * @param DOMDocument $document
	 *
	 * @return DOMElement
	 * @throws Exception
	 */
	private function getRootNode(DOMDocument $document) {
		$rootNodes = $document->documentElement->childNodes; // ->item(0)->childNodes;

		return $rootNodes;

		if ($rootNodes->length > 1) {
			throw new Exception('Template should have only one root node');
		}

		return $rootNodes->item(0);
	}

	/**
	 * @param DOMNode $node
	 * @param array $data
	 */
	private function handleNode(DOMNode $node, array $data, array $methods = []) {
		$this->replaceMustacheVariables($node, $data, $methods);

		if (!$this->isTextNode($node)) {
			$this->stripEventHandlers($node);
			$this->handleFor($node, $data, $methods);
			$this->handleRawHtml($node, $data, $methods);

			if (!$this->isRemovedFromTheDom($node)) {
				$this->handleAttributeBinding($node, $data, $methods);
				$this->handleIf($node->childNodes, $data, $methods);

				foreach (iterator_to_array($node->childNodes) as $childNode) {
					$this->handleNode($childNode, $data, $methods);
				}
			}
		}
	}

	private function stripEventHandlers(DOMNode $node) {
		if ($this->isTextNode($node)) {
			return;
		}
		/** @var DOMAttr $attribute */
		foreach ($node->attributes as $attribute) {
			if (strpos($attribute->name, 'v-on:') === 0) {
				$node->removeAttribute($attribute->name);
			}
		}
	}

	/**
	 * @param DOMNode $node
	 * @param array $data
	 */
	private function replaceMustacheVariables(DOMNode $node, array $data, array $methods = []) {
		// print_r($methods);
		if ($node instanceof DOMText) {
			$text = $node->wholeText;

			$regex = '/\{\{(?P<expression>.*?)\}\}/x';
			preg_match_all($regex, $text, $matches);

			foreach ($matches['expression'] as $index => $expression) {
				$value = $this->expressionParser->parse($expression, $methods)
					->evaluate($data, $methods);

				$text = str_replace($matches[0][$index], $value, $text);
			}

			if ($text !== $node->wholeText) {
				$newNode = $node->ownerDocument->createTextNode($text);
				$node->parentNode->replaceChild($newNode, $node);
			}
		}
	}

	private function handleAttributeBinding(DOMElement $node, array $data, array $methods = []) {
		/** @var DOMAttr $attribute */
		foreach (iterator_to_array($node->attributes) as $attribute) {
			if (!preg_match('/^:[\w-]+$/', $attribute->name)) {
				continue;
			}

			$value = $this->expressionParser->parse($attribute->value, $methods)
				->evaluate($data);

			$name = substr($attribute->name, 1);
			if (is_bool($value)) {
				if ($value) {
					$node->setAttribute($name, $name);
				}
			} else {
				$node->setAttribute($name, $value);
			}
			$node->removeAttribute($attribute->name);
		}
	}

	/**
	 * @param DOMNodeList $nodes
	 * @param array $data
	 */
	private function handleIf(DOMNodeList $nodes, array $data, array $methods = []) {
		// Iteration of iterator breaks if we try to remove items while iterating, so defer node
		// removing until finished iterating.
		$nodesToRemove = [];
		foreach ($nodes as $node) {
			if ($this->isTextNode($node)) {
				continue;
			}
			if ($node->nodeType == 7) continue;
			/** @var DOMElement $node */
			if ($node->hasAttribute('v-if')) {
				$conditionString = $node->getAttribute('v-if');
				$node->removeAttribute('v-if');
				$condition = $this->evaluateExpression($conditionString, $data, $methods);

				if (!$condition) {
					$nodesToRemove[] = $node;
				}

				$previousIfCondition = $condition;
			} elseif ($node->hasAttribute('v-else')) {
				$node->removeAttribute('v-else');

				if ($previousIfCondition) {
					$nodesToRemove[] = $node;
				}
			}
		}

		foreach ($nodesToRemove as $node) {
			$this->removeNode($node);
		}
	}

	private function handleFor(DOMNode $node, array $data, array $methods = []) {
		if ($this->isTextNode($node)) {
			return;
		}

		/** @var DOMElement $node */
		if ($node->hasAttribute('v-for')) {
			list($itemName, $listName) = explode(' in ', $node->getAttribute('v-for'));
			$node->removeAttribute('v-for');

			foreach ($data[$listName] as $item) {
				$newNode = $node->cloneNode(true);
				$node->parentNode->insertBefore($newNode, $node);
				$this->handleNode($newNode, array_merge($data, [$itemName => $item]), $methods);
			}

			$this->removeNode($node);
		}
	}

	private function appendHTML(DOMNode $parent, $source) {
		$tmpDoc = $this->parseHtml($source);
		foreach ($tmpDoc->getElementsByTagName('body')->item(0)->childNodes as $node) {
			$node = $parent->ownerDocument->importNode($node, true);
			$parent->appendChild($node);
		}
	}

	private function handleRawHtml(DOMNode $node, array $data, array $methods = []) {
		if ($this->isTextNode($node)) {
			return;
		}

		/** @var DOMElement $node */
		if ($node->hasAttribute('v-html')) {
			$variableName = $node->getAttribute('v-html');
			$node->removeAttribute('v-html');

			$newNode = $node->cloneNode(true);

			$this->appendHTML($newNode, $data[$variableName]);

			$node->parentNode->replaceChild($newNode, $node);
		}
	}

	/**
	 * @param string $expression
	 * @param array $data
	 *
	 * @return bool
	 */
	private function evaluateExpression($expression, array $data, array $methods = []) {
		return $this->expressionParser->parse($expression, $methods)->evaluate($data);
	}

	private function removeNode(DOMElement $node) {
		$node->parentNode->removeChild($node);
	}

	/**
	 * @param DOMNode $node
	 *
	 * @return bool
	 */
	private function isTextNode(DOMNode $node) {
		return $node instanceof DOMCharacterData;
	}

	private function isRemovedFromTheDom(DOMNode $node) {
		return $node->parentNode === null;
	}
}
