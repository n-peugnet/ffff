function GoToId(toucheClavier, last) { //permet de passer d'une image à une autre
	currentId = location.hash;
	if (currentId != '' && currentId != '#_') {
		if (toucheClavier == 27) {
			window.location.href = "#_";
		} else if (toucheClavier == 39 || toucheClavier == 37) {
			var cut = currentId.split("#img");
			var currentNb = parseInt(cut[1]);
			if (toucheClavier == 39) {
				if (currentNb < last) {
					window.location.href = "#img" + (currentNb + 1);
				}
			} else {
				if (currentNb > 1) {
					window.location.href = "#img" + (currentNb - 1);
				}
			}
		}
	}
}