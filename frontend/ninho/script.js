document.addEventListener("DOMContentLoaded", async () => {

    function converterParaDateBrasil(dataStr) {
        // dataStr vem assim: "07/06/2025 14:32:10"

        const [data, hora] = dataStr.split(" ");
        const [dia, mes, ano] = data.split("/");
        const [h, m, s] = hora.split(":");

        // Converte para formato americano válido: YYYY-MM-DDTHH:MM:SS
        return new Date(`${ano}-${mes}-${dia}T${h}:${m}:${s}`);
    }

    async function carregarDados(inicio = null, fim = null, intervalo = 20) {

        let url = "ninho.php?formato=json";

        if (!inicio) inicio = '2025-06-01';
        if (!fim) fim = '2025-06-07';

        url += `&inicio=${inicio}&fim=${fim}`;

        const resposta = await fetch(url);
        const json = await resposta.json();

        const dados = json.registros;

        const labelsBruto = dados.map(item => item.datahora_completa);
        const valoresBruto = dados.map(item => parseFloat(item.ninho));

        const step = parseInt(intervalo, 10) || 20;

        const labelsFiltradas = labelsBruto.filter((_, i) => i % step === 0);
        const valoresFiltrados = valoresBruto.filter((_, i) => i % step === 0);

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

        document.getElementById("valormax").textContent = json.maximo ?? '--';
        document.getElementById("valormin").textContent = json.minimo ?? '--';

        const ctx = document.getElementById("graficoTemperatura").getContext("2d");

        if (window.chart) {
            window.chart.destroy();
        }

        window.chart = new Chart(ctx, {
            type: "line",
            data: {
                labels: labelsFormatadas,
                datasets: [{
                    label: "Temperatura do Ninho (°C)",
                    data: valoresFiltrados,
                    borderColor: "orange",
                    borderWidth: 2.3,
                    tension: 0.25,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { title: { display: true, text: "Data e Hora" } },
                    y: { title: { display: true, text: "Temperatura (°C)" } }
                }
            }
        });
    }

    const form = document.getElementById("formPeriodo");

    form.addEventListener("submit", e => {
        e.preventDefault();

        const inicio = document.getElementById("inicio").value;
        const fim = document.getElementById("fim").value;
        const intervalo = parseInt(document.getElementById("intervalo").value, 10) || 20;

        carregarDados(inicio, fim, intervalo);
    });

    carregarDados();

});
