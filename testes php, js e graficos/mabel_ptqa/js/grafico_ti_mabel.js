
const form = document.getElementById('form-filtro');
const ctx_ti = document.getElementById('grafico_ti').getContext('2d');
let grafico_ti;

async function carregarTI(inicio, fim) {
  const url = `consulta_ti_mabel.php?inicio=${inicio}&fim=${fim}&formato=json`;
  const resposta = await fetch(url);
  const dados = await resposta.json();

  const labels = dados.map(l => l.datahora);
  const valores = dados.map(l => parseFloat(l.ti));

  if (grafico_ti) grafico_ti.destroy();

  grafico_ti = new Chart(ctx_ti, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Temperatura Interna (°C)',
        data: valores,
        borderColor: 'rgb(255, 99, 132)',
        backgroundColor: 'rgba(255, 99, 132, 0.2)',
        fill: true,
        tension: 0.3
      }]
    },
    options: { responsive: true }
  });

}
carregarTI('2025-06-01', '2025-06-30');

