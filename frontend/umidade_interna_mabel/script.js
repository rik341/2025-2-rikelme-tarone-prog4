document.addEventListener("DOMContentLoaded", async () => {

    async function carregarDados(inicio = null, fim = null) {

        let url = "umi_interna.php?formato=json";

        if (inicio && fim) {
            url += `&inicio=${inicio}&fim=${fim}`;
        }

        const resposta = await fetch(url);
        const json = await resposta.json();

        // ===========================
        // MÉDIA
        // ===========================
        const media = parseFloat(json.media);

        document.getElementById("MediaUmidade").textContent =
            isNaN(media) ? "--" : media.toFixed(2);

        // ===========================
        // DADOS BRUTOS
        // ===========================
        const dados = json.dados;

        const labelsBruto = dados.map(item => item.datahora_completa);
        const valoresBruto = dados.map(item => parseFloat(item.hi));

        // ===========================
        // FILTRAR DE 20 EM 20
        // ===========================
        const step = 20;

        const labels = labelsBruto.filter((_, i) => i % step === 0);
        const valores = valoresBruto.filter((_, i) => i % step === 0);

        // ===========================
        // GRÁFICO
        // ===========================
        const ctx = document.getElementById("graficoUmidadeInterna").getContext("2d");

        if (window.graficoUmidade) {
            window.graficoUmidade.destroy();
        }

        window.graficoUmidade = new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [{
                    label: "Umidade Interna (%)",
                    data: valores,
                    borderColor: "blue",
                    borderWidth: 2.5,
                    pointRadius: 0,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true
            }
        });
    }

    // ===========================
    // FORMULÁRIO DE FILTRO
    // ===========================
    const form = document.getElementById("formPeriodo");
    form.addEventListener("submit", e => {
        e.preventDefault();

        const inicio = document.getElementById("inicio").value;
        const fim = document.getElementById("fim").value;

        carregarDados(inicio, fim);
    });

    // Carrega primeira vez
    carregarDados();

});
