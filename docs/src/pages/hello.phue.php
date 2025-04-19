<app.layout :title="page.title" :page="page">
  <article>
    <h1>hello</h1>

    <p>to the world</p>

    <figure>
      <doc.image src="src/pages/kitty.jpg" size="600x" alt="this is the cat"></doc.image>

      <figcaption>Foto von <a href="https://unsplash.com/de/@yerlinmatu?utm_content=creditCopyText&utm_medium=referral&utm_source=unsplash">Yerlin Matu</a>
        auf <a href="https://unsplash.com/de/fotos/flachfokusfotografie-von-weissen-und-braunen-katzen-GtwiBmtJvaU?utm_content=creditCopyText&utm_medium=referral&utm_source=unsplash">Unsplash</a>
      </figcaption>
    </figure>

  </article>
</app.layout>

<style>
  p {
    border: 1px solid black;
  }
</style>