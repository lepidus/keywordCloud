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
	<link rel="stylesheet" type="text/css" href="/plugins/blocks/keywordCloud/styles/style.css">
	<span class="title">{translate key="plugins.block.keywordCloud.title"}</span>
	<div class="content" id='wordcloud'></div>

	<script>
	function randomColor(){ldelim}
		var cores = ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b', '#e377c2', '#7f7f7f', '#bcbd22', '#17becf'];
		return cores[Math.floor(Math.random()*cores.length)];
	{rdelim}

	document.addEventListener("DOMContentLoaded", function() {ldelim}
		var keywords = {$keywords};
		var totalWeight = 0;

		var length_keywords = keywords.length;

		keywords.forEach(function(item,index){ldelim}totalWeight += item.size;{rdelim});

		var svg = d3.select("#wordcloud").append("svg")
			.attr("viewBox", '0 0 300 200')	
			.attr("width", '100%');

		var width = 300;
		var height = 200;

		var layout = d3.layout.cloud()
				.size([width, height])
				.words(keywords)
				.padding(1)
				.fontSize(function(d){ldelim}

					const functionMinimum = 0.20, functionMaximum = 0.35;
					const sizeMinimum = 10, sizeMedium = 20, sizeMaximum = 35;
				
					var frequency = d.size/totalWeight;
					var weight = frequency * (height/length_keywords);

					if(weight < functionMinimum) return sizeMinimum;
					if(weight > functionMaximum) return sizeMaximum; 
					
					return sizeMedium;
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
				.style("font-family", 'serif')
				.style("fill", randomColor)
				.style('cursor', 'pointer')
				.attr('class', 'keyword')
				.attr("text-anchor", "middle")
				.attr("transform", function(d) {ldelim}
					return "translate(" + [d.x, d.y] + ")rotate(" + d.rotate + ")";
				{rdelim}) 
				.text(function(d) {ldelim} return d.text; {rdelim})
				.on("click", function(d, i){ldelim}
					window.location = "{url router=$smarty.const.ROUTE_PAGE page="search" query="QUERY_SLUG"}".replace(/QUERY_SLUG/, encodeURIComponent(''+d.text+''));
				{rdelim})
				.on("mouseover", function(d, i) {ldelim}
					d3.select(this).transition().style('font-size',function(d) {ldelim} return (1.25*d.size) + "px"; {rdelim});
				{rdelim})
				.on("mouseout", function(d, i) {ldelim}
					d3.select(this).transition().style('font-size',function(d) {ldelim} return d.size + "px"; {rdelim});
				{rdelim});

		{rdelim}

		layout.start();

	{rdelim});

	</script>

	
</div>