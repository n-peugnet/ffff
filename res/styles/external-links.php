	<style>
		a[href^="http"]:not([href^="<?= FFRouter::getBasePath() ?>"]):after {
			font-family: 'external-links' !important;
			-webkit-font-smoothing: antialiased;
			-moz-osx-font-smoothing: grayscale;
			content: "\ea7e";
		}
	</style>