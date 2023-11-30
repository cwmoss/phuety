import re
import pprint

def toTree(infixStr):
    # divide string into tokens, and reverse so I can get them in order with pop()
    tokens = re.split(r' *([\+\-\*&\^/]) *', infixStr)
    tokens = [t for t in reversed(tokens) if t!='']
    print tokens
    print tokens[-1]
    
    precs = {'+':0 , '-':0, '&':0, '/':1, '*':1, '^':2}
 
    #convert infix expression tokens to a tree, processing only
    #operators above a given precedence
    def toTree2(tokens, minprec):
        node = tokens.pop()
        while len(tokens)>0:
            prec = precs[tokens[-1]]
            if prec<minprec:
                break
            op=tokens.pop()
 
            # get the argument on the operator's right
            # this will go to the end, or stop at an operator
            # with precedence <= prec
            arg2 = toTree2(tokens,prec+1)
            node = (op, node, arg2)
        return node
 
    return toTree2(tokens,0)

input = "5+3*4^2+1"
print input
print toTree(input)

pp = pprint.PrettyPrinter(depth=10, indent=4, width=8)

pp.pprint(toTree(input))
print toTree("5+ 13 & 4^2+1")