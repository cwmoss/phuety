<webco-form :success="props.success">
    <section class="result-success hdn"></section>


    <slot></slot>


</webco-form>

<script>
    /*
    https://pqina.nl/blog/async-form-posts-with-a-couple-lines-of-vanilla-javascript/
    */
    class WebcoForm extends HTMLElement {
        connectedCallback() {
            //let targetEl = document.querySelector(this.getAttribute('target'));
            let form = this.querySelector('form');
            form.insertAdjacentHTML('beforeend', '<section class="result-error hdn"></section>')
            let success_msg = this.getAttribute('success');
            let success_container = this.querySelector('.result-success');
            let error_container = this.querySelector('.result-error');

            form.addEventListener("submit", (e) => {
                // targetEl.style.setProperty('font-size', slider.value + unit);
                console.log("submitting!", e, form.method, success_msg, new FormData(form))
                let button = e.submitter
                button.classList.add("button--loading")
                fetch(form.action, {
                        method: form.method,
                        body: new FormData(form),
                        headers: {
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(response => {
                        console.log(response)

                        if (response.errors) {
                            error_container.innerHTML = Object.values(response.errors).join("<br>")
                            error_container.classList.remove('hdn')
                        } else {
                            success_container.innerHTML = success_msg
                            success_container.classList.remove('hdn')
                            form.classList.add('hdn')
                        }
                    })
                    .catch(err => {
                        // Unlock form elements
                        Array.from(form.elements).forEach(field => field.disabled = false);
                        // Show error message
                        form.querySelector('[role=alert]').hidden = false;
                    })
                    .finally(() => {
                        Array.from(form.elements).forEach(field => field.disabled = false);
                        button.classList.remove("button--loading")
                    })
                Array.from(form.elements).forEach((field) => (field.disabled = true));
                e.preventDefault()
            });
        }
    }

    customElements.define("webco-form", WebcoForm);

    console.log("webco++");
</script>

<style>
    /*
    .hdn {
        opacity: 0;
        height: 0;
        overflow: hidden;
        transition: opacity 1s ease-in;
    }
*/
    section {
        opacity: 1;
        height: auto;
    }


    section {
        visibility: visible;
        opacity: 1;
        transition: opacity 0.3s ease-in,
            visibility 0.3s ease-in;
    }

    section.hdn,
    form.hdn {
        visibility: hidden;
        opacity: 0;
        height: 0;
        padding: 0 !important;
    }


    section.result-error {
        background-color: fuchsia;
        color: white;
        justify-content: left;
        padding: 2em;
    }

    section.result-success {
        padding: 5em;
        font-weight: bold;
        justify-content: left;
    }
</style>