document.addEventListener('DOMContentLoaded', function () {
    // Atualiza a data final (2 meses a partir da data atual)
    function updateDeadline() {
        const today = new Date();

        const day = String(today.getDate()).padStart(2, '0');
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const year = today.getFullYear();

        const deadlineElement = document.getElementById('statusDeadline');
        deadlineElement.textContent = `Prazo final: ${day}/${month}/${year}`;
    }


    // Inicializa a página
    updateDeadline();

    // Adiciona evento ao botão de regularização
    const regularizarButton = document.querySelector('.regularizar-button');
    if (regularizarButton) {
        regularizarButton.addEventListener('click', function () {
            window.location.href = 'carregamento.html';
        });
    }
});


