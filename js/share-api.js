if (navigator && navigator.share !== undefined) {
	var buttons = document.getElementsByClassName('dev-share-buttons');
	for (var i = buttons.length - 1; i >= 0; i--) {
		var container = buttons[i];
		var shareData = {
			url: document.querySelector('link[rel=canonical]') ? document.querySelector('link[rel=canonical]').href : window.location.href,
			title: document.title,
		};
		if (container.dataset && container.dataset.shareTitle) {
			shareData.title = container.dataset.shareTitle;
		}
		if (container.dataset && container.dataset.shareText) {
			shareData.text = container.dataset.shareText;
		}
		var shareApiButton = document.createElement('button');
		shareApiButton.className = 'dev-share-buttons__item dev-share-buttons__item--share-api';
		shareApiButton.innerText = 'Share';
		for (var i = container.children.length - 1; i >= 0; i--) {
			container.children[i].style.display = 'none';
		}
		container.append(shareApiButton);
		shareApiButton.addEventListener('click', function(e) {
			navigator.share(shareData)
				.catch(function(err) {
					alert('Uh oh! There was an error using the share api. Maybe try using these default links instead');
					shareApiButton.remove();
					for (var i = container.children.length - 1; i >= 0; i--) {
						container.children[i].style.display = null;
					}
				});
		});
	}
}
