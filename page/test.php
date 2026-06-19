<div class="pl-2 pr-2 pt-2 pb-2">

    <div class="d-flex justify-content-center">
        <h1>Test Dashboard</h1>
    </div>

    <div class="d-flex justify-content-center">
        <div class="card">
            <div class="card-title text-center pt-2 bg-success">
                <h5 class="font-weight-bold">Test Card</h5>
            </div>
            <div class="card-body shadow-sm text-center">
                <p>Test Card Body</p>
                <p>The quick brown fox jumps over the lazy dog</p>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center">
        <ul class="list-group pt-2 pl-2 pr-2">
            <li class="list-group-item">Cras justo odio</li>
            <li class="list-group-item">Dapibus ac facilisis in</li>
            <li class="list-group-item">Morbi leo risus</li>
            <li class="list-group-item">Porta ac consectetur ac</li>
            <li class="list-group-item">Vestibulum at eros</li>
        </ul>
    </div>

    <div class="d-flex justify-content-center pt-2">
        <button class="btn btn-success" id="testbutton">
            <i class="fa-solid fa-mouse-pointer">
            </i>
            <span>
                Click Me!
            </span>
        </button>
    </div>

</div>


<script>
    $(document).off("click", "#testbutton").on("click", "#testbutton", function(e) {

        $.ajax({
            url: "",
            method: "POST",
            data: {
                request: "userlist"
            },
            success: function(dataresult) {
                Swal.fire({
                    title: "Success!",
                    text: "The button has been clicked!",
                    icon: "success"
                });
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    title: "Error!",
                    text: "There's an error clicking the button!",
                    icon: "error"
                });
                console.error('AJAX Error:', error);
            }

        });

    });
</script>
