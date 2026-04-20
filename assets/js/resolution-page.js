(function () {
	"use strict";

	var config = window.wpshadowResolutionPage || {};
	var ajaxUrl = config.ajaxUrl || "";
	var i18n = config.i18n || {};

	function t(key, fallback) {
		return Object.prototype.hasOwnProperty.call(i18n, key) ? i18n[key] : fallback;
	}

	document.querySelectorAll(".wps-res-card__header").forEach(function (header) {
		function toggle() {
			var body = document.getElementById(header.getAttribute("aria-controls"));
			if (!body) {
				return;
			}

			var isOpen = body.hasAttribute("hidden");
			body.toggleAttribute("hidden", !isOpen);
			header.setAttribute("aria-expanded", isOpen ? "true" : "false");
		}

		header.addEventListener("click", toggle);
		header.addEventListener("keydown", function (event) {
			if (event.key === "Enter" || event.key === " ") {
				event.preventDefault();
				toggle();
			}
		});
	});

	document.querySelectorAll(".wps-rc-btn").forEach(function (button) {
		button.addEventListener("click", function () {
			var card = button.closest(".wps-res-card");
			var message = card ? card.querySelector(".wps-res-feedback-msg") : null;
			button.disabled = true;

			var formData = new FormData();
			formData.append("action", "wpshadow_resolution_save");
			formData.append("nonce", button.dataset.nonce);
			formData.append("diagnostic_slug", button.dataset.slug);
			formData.append("status", button.dataset.action);

			fetch(ajaxUrl, { method: "POST", body: formData })
				.then(function (response) { return response.json(); })
				.then(function (data) {
					button.disabled = false;
					if (data.success) {
						if (message) {
							message.textContent = (data.data && data.data.message) || t("saved", "Saved.");
							message.style.display = "inline";
						}
						setTimeout(function () { window.location.reload(); }, 1200);
						return;
					}
					window.alert((data.data && data.data.message) || t("couldNotSave", "Could not save."));
				})
				.catch(function () {
					button.disabled = false;
					window.alert(t("networkError", "Network error."));
				});
		});
	});

	document.querySelectorAll(".wps-rc-save-option").forEach(function (button) {
		button.addEventListener("click", function () {
			var row = button.closest(".wps-res-option-row");
			var feedback = row ? row.querySelector(".wps-res-option-feedback") : null;
			button.disabled = true;
			button.textContent = t("saving", "Saving...");

			var formData = new FormData();
			formData.append("action", "wpshadow_resolution_update_option");
			formData.append("nonce", button.dataset.nonce);
			formData.append("option_name", button.dataset.option);
			formData.append("option_value", button.dataset.value);

			fetch(ajaxUrl, { method: "POST", body: formData })
				.then(function (response) { return response.json(); })
				.then(function (data) {
					button.disabled = false;
					if (data.success) {
						button.textContent = t("savedButton", "Saved");
						if (feedback) {
							feedback.textContent = (data.data && data.data.message) || t("updated", "Updated.");
							feedback.style.display = "inline";
						}
						setTimeout(function () { window.location.reload(); }, 1400);
						return;
					}

					button.textContent = t("saveButton", "Save");
					if (feedback) {
						feedback.textContent = (data.data && data.data.message) || t("couldNotSave", "Could not save.");
						feedback.classList.add("error");
						feedback.style.display = "inline";
					}
				})
				.catch(function () {
					button.disabled = false;
					button.textContent = t("saveButton", "Save");
					if (feedback) {
						feedback.textContent = t("networkError", "Network error.");
						feedback.classList.add("error");
						feedback.style.display = "inline";
					}
				});
		});
	});
})();
