<script>
		Swal.fire({
		  title: "Logged out!",
		  text: "Your session ended.",
		  icon: "error",
		  confirmButtonText: "See you next time!",
		  scrollbarPadding: false
		})	
		.then(() => {
			location.reload();
		});
</script>