document.addEventListener('DOMContentLoaded', function () {
    const steps = document.querySelectorAll('.loading-step');

    // Função para mostrar os passos sequencialmente
    function showStepsSequentially() {
        steps[0].classList.add('active');

        setTimeout(() => {
            steps[1].classList.add('active');

            // Após mostrar o segundo passo, aguarda um momento e redireciona
            setTimeout(() => {
                window.location.href = 'resultado.html';
            }, 2000);

        }, 2000); // Mostra o segundo passo após 2 segundos
    }

    // Inicia a sequência
    showStepsSequentially();
});