document.addEventListener("DOMContentLoaded", async () => {

    function converterParaDateBrasil(dataStr) {
        const [data, hora] = dataStr.split(" ");
        const [dia, mes, ano] = data.split("/");
        const [h, m, s] = hora.split(":");

        return new Date(`${ano}-${mes}-${dia}T${h}:${m}:${s}`);
    }

    async function carregarDados(inicio = null, fim = null, intervalo = 20) {

        let url = "umi_interna.php?formato=json";

        if (!inicio) inicio = '2025-06-01';
        if (!fim) fim = '2025-06-07';

        url += `&inicio=${inicio}&fim=${fim}`;

        const resposta = await fetch(url);
        const json = await resposta.json();

        // ==========================
        // MÉDIA FORMATADA
        // ==========================
        const media = parseFloat(json.media);

        document.getElementById("MediaUmidade").textContent =
            isNaN(media) ? "--" : media.toFixed(1);

        // ==========================
        // DADOS BRUTOS
        // ==========================
        const dados = json.dados;

        const labelsBruto = dados.map(item => item.datahora_completa);
        const valoresBruto = dados.map(item => parseFloat(item.hi));

        // INTERVALO
        intervalo = parseInt(intervalo) || 20;

        const labelsFiltradas = labelsBruto.filter((_, i) => i % intervalo === 0);
        const valoresFiltrados = valoresBruto.filter((_, i) => i % intervalo === 0);

        // ==========================
        // FORMATAR DATAS
        // ==========================
        const labelsFormatadas = labelsFiltradas.map(label => {
            const dt = converterParaDateBrasil(label);

            return dt.toLocaleString("pt-BR", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit"
            });
        });

        // ==========================
        // GRÁFICO
        // ==========================
        const ctx = document.getElementById("graficoUmidadeInterna").getContext("2d");

        if (window.graficoUmidade) {
            window.graficoUmidade.destroy();
        }

        window.graficoUmidade = new Chart(ctx, {
            type: "line",
            data: {
                labels: labelsFormatadas,
                datasets: [{
                    label: "Umidade Interna (%)",
                    data: valoresFiltrados,
                    borderColor: "blue",
                    borderWidth: 2.5,
                    pointRadius: 0,
                    tension: 0.25
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { title: { display: true, text: "Data e Hora" } },
                    y: { title: { display: true, text: "Umidade (%)" } }
                }
            }
        });
    }

    const form = document.getElementById("formPeriodo");
    form.addEventListener("submit", e => {
        e.preventDefault();

        const inicio = document.getElementById("inicio").value;
        const fim = document.getElementById("fim").value;
        const intervalo = document.getElementById("intervalo").value;

        carregarDados(inicio, fim, intervalo);
    });

    carregarDados();
});
