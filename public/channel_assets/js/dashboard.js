(() => {
    const source = document.getElementById('dashboard-chart-data');
    if (!source || typeof Chart === 'undefined' || !document.getElementById('dashboard-shop-chart')) return;
    const data = JSON.parse(source.textContent);
    const colors = ['#6255a5','#3a9c9a','#d59a40','#bd6589','#657fb5','#8b9b4d','#cd775c'];
    new Chart(document.getElementById('dashboard-shop-chart'), {
        type: 'pie',
        data: {labels: data.shops.labels, datasets: [{data: data.shops.data, backgroundColor: colors}]},
        options: {responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'bottom', labels:{boxWidth:10,font:{size:11}}}}}
    });
    new Chart(document.getElementById('dashboard-category-chart'), {
        type:'line',
        data:{labels:Array.from({length:12},(_,i)=>`${i+1}월`), datasets:data.categories.map((series,i)=>({label:series.label,data:series.data,borderColor:colors[i%colors.length],backgroundColor:colors[i%colors.length],borderWidth:2,tension:.2,fill:false}))},
        options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true,ticks:{precision:0}}},plugins:{legend:{position:'bottom',labels:{boxWidth:10,font:{size:11}}}}}
    });
})();
