document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formPeriodo");
  const ctx = document.getElementById("graficoTemperatura").getContext("2d");

  // CAMPOS DO HTML
  const valorMediaTe = document.getElementById("valorMedia");
  const valorMediaDiferenca = document.getElementById("valorMediaDif");

  // URLs DOS PHP
  const URL_GRAFICO = "/2025-2-rikelme-tarone-prog4/backend/consultas_mabel/consulta_te_mabel.php";
  const URL_MEDIA_TE = "/2025-2-rikelme-tarone-prog4/backend/consultas_mabel/consulta_media_te_mabel.php";
  const URL_MEDIA_DIF = "/2025-2-rikelme-tarone-prog4/backend/consultas_mabel/consulta_ti_te_diferença.php";

  let chart;

  // ============================================================
  // 1) CARREGAR GRÁFICO (Temperatura Externa)
  // ============================================================
  async function carregarGrafico(inicio, fim) {
    try {
      const url = `${URL_GRAFICO}?formato=json&inicio=${inicio}&fim=${fim}`;
      const resposta = await fetch(url);
      const dados = await resposta.json();

      const labels = dados.map(item => item.datahora_completa);
      const valores = dados.map(item => parseFloat(item.te));

      if (chart) chart.destroy();

      // reduzir pontos manualmente: 1 a cada 20
const step = 20;
const labelsReduzidos = labels.filter((_, i) => i % step === 0);
const valoresReduzidos = valores.filter((_, i) => i % step === 0);

chart = new Chart(ctx, {
  type: "line",
  data: {
    labels: labelsReduzidos,
    datasets: [{
      label: "Temperatura Externa (°C)",
      data: valoresReduzidos,
      borderColor: "blue",
      borderWidth: 2.5,
      tension: 0.3,
      pointRadius: 2 // remove os pontos!
    }]
  },
  options: {
    responsive: true,
    scales: {
      x: { title: { display: true, text: "Data e Hora" } },
      y: { title: { display: true, text: "Temperatura (°C)" } }
    },
    plugins: {
      decimation: {
        enabled: true,
        algorithm: "min-max",
      }
    }
  }
});


    } catch (e) {
      console.error("Erro no gráfico:", e);
      alert("Erro ao carregar gráfico");
    }
  }

  // ============================================================
  // 2) CARREGAR MÉDIA DA TEMPERATURA EXTERNA
  // ============================================================
  async function carregarMediaTe(inicio, fim) {
    try {
      const resposta = await fetch(`${URL_MEDIA_TE}?formato=json&inicio=${inicio}&fim=${fim}`);
      const dados = await resposta.json();

      valorMediaTe.textContent = dados.media_temperatura_externa
        ? dados.media_temperatura_externa
        : "--";

    } catch (e) {
      console.error(e);
      valorMediaTe.textContent = "--";
    }
  }

  // ============================================================
  // 3) CARREGAR DIFERENÇA MÉDIA (te – ti)
  // ============================================================
  async function carregarMediaDiferenca(inicio, fim) {
    try {
      const resposta = await fetch(`${URL_MEDIA_DIF}?formato=json&inicio=${inicio}&fim=${fim}`);
      const dados = await resposta.json();

      valorMediaDiferenca.textContent = dados.media_diferenca
        ? Number(dados.media_diferenca).toFixed(2)
        : "--";

    } catch (e) {
      console.error(e);
      valorMediaDiferenca.textContent = "--";
    }
  }

  // ============================================================
  // 4) FORM SUBMIT
  // ============================================================
  form.addEventListener("submit", e => {
    e.preventDefault();

    const inicio = document.getElementById("inicio").value;
    const fim = document.getElementById("fim").value;

    carregarGrafico(inicio, fim);
    carregarMediaTe(inicio, fim);
    carregarMediaDiferenca(inicio, fim);
  });

  // ============================================================
  // 5) DEFINIR DATA PADRÃO FIXA (01 a 07 de junho de 2025)
  // ============================================================
  document.getElementById("inicio").value = "2025-06-01";
  document.getElementById("fim").value = "2025-06-07";

  // Carregar inicial automaticamente
  carregarGrafico("2025-06-01", "2025-06-07");
  carregarMediaTe("2025-06-01", "2025-06-07");
  carregarMediaDiferenca("2025-06-01", "2025-06-07");
});
