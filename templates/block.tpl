{**
 * plugins/blocks/KeywordCloud/block.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site sidebar menu -- keywords cloud.
 *
 *}
<div class="pkp_block block_Keywordcloud">
	<span class="title">{translate key="plugins.block.keywordCloud.title"}</span>
	<div class="content" id='wordcloud'></div>
	<script>
	function randomColor(){ldelim}
		var cores = ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b', '#e377c2', '#7f7f7f', '#bcbd22', '#17becf'];
		return cores[Math.floor(Math.random()*cores.length)];
	{rdelim}

	document.addEventListener("DOMContentLoaded", function() {ldelim}
		var keywords = {$keywords};
		var pesoTotal = 0;

		keywords.forEach(function(item,index){ldelim}pesoTotal += item.size{rdelim});

		var svg = d3.select("#wordcloud").append("svg")
			.attr("width", '100%')
			.attr("height", '100%');	

		var width = document.getElementById('wordcloud').clientWidth;
		var height = document.getElementById('wordcloud').clientHeight;

		var layout = d3.layout.cloud()
				.size([width, height])
				.words(keywords)
				.padding(2)
				.fontSize(function(d){ldelim}
					var minimo = 0.1 * height, maximo = 0.3 * height;
					var peso = (d.size/pesoTotal) * height;
					
					if(peso < minimo) return minimo;
					if(peso > maximo) return maximo;
					return peso;
				{rdelim})
				.on('end', draw);

		function draw(words) {ldelim}
			svg
			.append("g")
			.attr("transform", "translate(" + layout.size()[0] / 2 + "," + layout.size()[1] / 2 + ")")
			.selectAll("text")
				.data(words)
			.enter().append("text")
				.style("font-size", function(d) {ldelim} return d.size + "px"; {rdelim})
				.style("fill", randomColor)
				.attr("text-anchor", "middle")
				.attr("transform", function(d) {ldelim}
					return "translate(" + [d.x, d.y] + ")rotate(" + d.rotate + ")";
				{rdelim})
				.text(function(d) {ldelim} return d.text; {rdelim});
		{rdelim}

		layout.start();

	{rdelim});
	</script>
</div>