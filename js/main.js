// import chart functionality
import Chart from 'chart.js/auto';

// attach event listener to tab navigation for rankings
$('#athleteRankings a').on('click', function (e) {
    e.preventDefault();
    $(this).tab('show');
});