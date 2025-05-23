<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap"
    rel="stylesheet" />
  <title>phuety – your friendly html5 template engine</title>
  <style>
    body {
      font-family: "Figtree", sans-serif;
      font-optical-sizing: auto;
      font-weight: 600;
      font-style: normal;
      margin: 0;
    }

    header,
    footer {
      max-width: 500px;
      margin: 0 auto;
      padding: 3rem 0;
    }

    header {
      padding-bottom: 0;
    }

    nav a {
      color: black;
      text-decoration: none;
    }

    section {
      background-color: black;
      color: white;
      display: flex;

      .big {
        padding: 2rem;
        width: 35%;
      }
    }

    .big {
      font-size: 3em;
      font-weight: 800;
    }

    .code {
      font-size: 1.5em;
    }

    nav span {
      font-weight: 900;
    }

    section[bbl] {
      background: white;
      /* #4fc2eb; #eb2f3c*/
      padding: 0;
    }

    xsection:nth-of-type(2) {
      background: #fff;
    }

    xsection:nth-of-type(3) {
      background: #0388fe;
    }

    blockquote.bubble {
      background-position: center;
      background-repeat: no-repeat !important;
      background-size: 100% 100%;
      margin: 0 auto;
      text-align: center;
      height: 0;
      box-sizing: content-box;
      line-height: 1;
    }

    blockquote.speech {
      background: url(https://s3-us-west-2.amazonaws.com/s.cdpn.io/4273/speech-bubble.svg);
      width: 25%;
      padding-top: 6%;
      padding-bottom: 20%;
      font-size: 1rem;
      color: red;
    }
  </style>
  <!-- https://freefrontend.com/css-speech-bubbles/ -->
</head>

<body>
  <header>
    <nav><span>phuety</span> <a href="docs/">docs</a> <a href="https://github.com/cwmoss/phuety">github</a></nav>
    <section bbl>
      <blockquote class="speech bubble">OMG <br /><em>hahaha</em></blockquote>
    </section>
  </header>
  <main>
    <section>
      <div class="big">
        Finally. Single File Components for the PHP.<br />

        You know vue? You'll feel right at home.<br />

        Haveing Fun again.
      </div>
      <div class="code">
        <pre><code id="code"></code></pre>
      </div>
    </section>
  </main>
  <footer>since 2025</footer>
  <script id="example" hidden type="sfc">
    <h2>Hello {{ name }}</h2>
      <my.greeting></my.greeting>
      <?php
      $a = "dd";
      ?>
    </script>

  <script>
    code.textContent = example.innerHTML;
  </script>
</body>

</html>