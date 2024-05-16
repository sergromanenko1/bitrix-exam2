document.addEventListener("DOMContentLoaded", () => {
	class NewsDetailEpilog
	{
		constructor()
		{
			this.$complaint = document.querySelector("#complaint");

			if (!this.$complaint) {
				return;
			}

			this.$complaint.addEventListener("click", (e) => {
				this.complain(e);
			});

			const COMPLAINT_ID = location.search.match(/complaint_id=(\d+|error)/);

			if (COMPLAINT_ID && COMPLAINT_ID[1]) {
				this.showMessage(COMPLAINT_ID[1]);
			}
		}

		showMessage(complaintId)
		{
			let $next = this.$complaint.nextElementSibling;
	
			if (!$next || !$next.matches("#complaint-message")) {
				$next = document.createElement("p");
				$next.id = "complaint-message";
				this.$complaint.after($next);
			}
	
			$next.innerHTML = "error" === complaintId ?
				this.$complaint.getAttribute("data-error") :
				this.$complaint.getAttribute("data-message").replace("#COMLAINT_ID#", complaintId);
		}
	
		complain(e)
		{
			if ("Y" !== this.$complaint.getAttribute("data-ajax")) {
				return;
			}
		
			const xhr = new XMLHttpRequest();
	
			e.preventDefault();
			xhr.open("GET", this.$complaint.href);
	
			xhr.addEventListener("load", () => {
				this.showMessage(xhr.responseText);
			});
	
			xhr.addEventListener("error", () => {
				this.showMessage("error");
			});
			xhr.send();
		}
	}
	new NewsDetailEpilog();
});
