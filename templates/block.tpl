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
	document.addEventListener("DOMContentLoaded", function() {ldelim}
		d3.wordcloud()
			.size([300, 200])
			.selector('#wordcloud')
			.scale('linear')
			.fill(d3.scale.ordinal().range([ "#953255","#AA9139", "#2F3F73" , "#257059"]))
			.words({$keywords})
			.onwordclick(function(d, i) {ldelim}
				window.location = "{url router=$smarty.const.ROUTE_PAGE page="search" query="QUERY_SLUG"}".replace(/QUERY_SLUG/, encodeURIComponent('*'+d.text+'*'));
			{rdelim})
			.start();
	{rdelim});
	</script>
</div>