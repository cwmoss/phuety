
    /*
    https://pqina.nl/blog/async-form-posts-with-a-couple-lines-of-vanilla-javascript/
    */
    class WebcoForm extends HTMLElement {
        connectedCallback() {
            //let targetEl = document.querySelector(this.getAttribute('target'));
            let form = this.querySelector('form');
            form.insertAdjacentHTML('beforeend', '<section class="result-error hdn">')
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
                        // Show error message for failed requests
                        // form.querySelector('[role=alert]').hidden = false;
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
