document.addEventListener('DOMContentLoaded', function () {
    const cpfInput = document.getElementById('cpf');
    const consultButton = document.getElementById('consultButton');
    const secureHeader = document.querySelector('.secure-header');
    const infoText = document.querySelector('.info-text');
    const arrow = document.querySelector('.arrow');

    // CPF mask
    cpfInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
            e.target.value = value;
        }
    });

    // Toggle secure info
    secureHeader.addEventListener('click', function () {
        infoText.style.display = infoText.style.display === 'none' ? 'block' : 'none';
        arrow.textContent = infoText.style.display === 'none' ? '▼' : '▲';
    });

    // Validate CPF and enable/disable button
    cpfInput.addEventListener('input', function () {
        const cpf = this.value.replace(/\D/g, '');
        consultButton.disabled = cpf.length !== 11;

        if (cpf.length === 11) {
            consultButton.style.backgroundColor = '#1a73e8';
        } else {
            consultButton.style.backgroundColor = '#8c8c8c';
        }
    });

    // Form submission
    consultButton.addEventListener('click', function (e) {
        e.preventDefault();
        const cpf = cpfInput.value.replace(/\D/g, '');

        if (cpf.length === 11 && validateCPF(cpf)) {

        } else {
            alert('Por favor, insira um CPF válido.');
        }
    });

    // CPF validation function
    function validateCPF(cpf) {
        if (cpf.length !== 11) return false;

        // Check if all digits are the same
        if (/^(\d)\1{10}$/.test(cpf)) return false;

        // Validate first digit
        let sum = 0;
        for (let i = 0; i < 9; i++) {
            sum += parseInt(cpf.charAt(i)) * (10 - i);
        }
        let rev = 11 - (sum % 11);
        if (rev === 10 || rev === 11) rev = 0;
        if (rev !== parseInt(cpf.charAt(9))) return false;

        // Validate second digit
        sum = 0;
        for (let i = 0; i < 10; i++) {
            sum += parseInt(cpf.charAt(i)) * (11 - i);
        }
        rev = 11 - (sum % 11);
        if (rev === 10 || rev === 11) rev = 0;
        if (rev !== parseInt(cpf.charAt(10))) return false;

        return true;
    }
});


document.addEventListener('DOMContentLoaded', function () {
    const cpfInput = document.getElementById('cpf');
    const consultButton = document.getElementById('consultButton');
    const secureHeader = document.querySelector('.secure-header');
    const infoText = document.querySelector('.info-text');
    const arrow = document.querySelector('.arrow');

    // CPF mask
    cpfInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
            e.target.value = value;
        }
    });

    // Toggle secure info
    secureHeader.addEventListener('click', function () {
        infoText.style.display = infoText.style.display === 'none' ? 'block' : 'none';
        arrow.textContent = infoText.style.display === 'none' ? '▼' : '▲';
    });

    // Validate CPF and enable/disable button
    cpfInput.addEventListener('input', function () {
        const cpf = this.value.replace(/\D/g, '');
        consultButton.disabled = cpf.length !== 11;

        if (cpf.length === 11) {
            consultButton.style.backgroundColor = '#1a73e8';
        } else {
            consultButton.style.backgroundColor = '#8c8c8c';
        }
    });

    // CPF validation function
    function validateCPF(cpf) {
        if (cpf.length !== 11) return false;
        if (/^(\d)\1{10}$/.test(cpf)) return false;

        let sum = 0;
        for (let i = 0; i < 9; i++) sum += parseInt(cpf.charAt(i)) * (10 - i);
        let rev = 11 - (sum % 11);
        if (rev >= 10) rev = 0;
        if (rev !== parseInt(cpf.charAt(9))) return false;

        sum = 0;
        for (let i = 0; i < 10; i++) sum += parseInt(cpf.charAt(i)) * (11 - i);
        rev = 11 - (sum % 11);
        if (rev >= 10) rev = 0;
        if (rev !== parseInt(cpf.charAt(10))) return false;

        return true;
    }

    // Consulta e salvamento
    consultButton.addEventListener('click', async (e) => {
        e.preventDefault();
        const raw = cpfInput.value.replace(/\D/g, '');

        if (!validateCPF(raw)) {
            alert('Por favor, insira um CPF válido.');
            return;
        }

        consultButton.disabled = true;
        consultButton.textContent = 'Consultando...';

        try {
            const res = await fetch('cpf.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cpf: raw })
            });

            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                throw new Error(err.error || 'Erro na requisição ao servidor.');
            }

            const data = await res.json();

            // Extrair os dados
            const nome = data.nome || 'Não informado';
            const cpf = data.cpf || cpfInput.value;
            const nascimento = data.nascimento || 'Não informado';
            const sexo = data.sexo || 'Não informado';
            const endereco = data.endereco || data.logradouro || 'Não informado';

            // === Salvar cada campo separadamente no localStorage ===
            localStorage.setItem('nome', nome);
            localStorage.setItem('cpf', cpf);
            localStorage.setItem('nascimento', nascimento);
            localStorage.setItem('sexo', sexo);
            localStorage.setItem('endereco', endereco);
            localStorage.setItem('dataConsulta', new Date().toLocaleString());



            window.location.href = 'loading.html';

        } catch (err) {
            console.error(err);
            alert('Erro: ' + err.message);
        } finally {
            consultButton.disabled = false;
            consultButton.textContent = 'CONSULTAR AGORA';
        }
    });
});
