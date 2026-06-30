<div class="pl-2 pr-2 pt-2 pb-2">

    <div class="d-flex justify-content-center">
        <h1>Test Dashboard</h1>
    </div>

    <div class="d-flex justify-content-center">
        <div class="card">
            <div class="card-title text-center pt-2 bg-success">
                <h5 class="font-weight-bold">Placeholder Card</h5>
            </div>
            <div class="card-body shadow-sm text-center">
                <p>Test Card Body</p>
                <p>The quick brown fox jumps over the lazy dog</p>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center">
        <ul class="list-group pt-2 pl-2 pr-2">
            <li class="list-group-item">Test 1</li>
            <li class="list-group-item">Test 2</li>
            <li class="list-group-item">Test 3</li>
            <li class="list-group-item">Test 4</li>
            <li class="list-group-item">Test 5</li>
        </ul>
    </div>

    <div class="d-flex justify-content-center pt-2">
        <button class="btn btn-success" id="testbutton">
            <i class="fa-solid fa-mouse-pointer">
            </i>
            <span>
                Button Click
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
                request: "userlist",
                user_id: UserInfo["UserID"]
            },
            success: function(dataresult) {
                Swal.fire({
                    title: "Success!",
                    text: "The button has been clicked!",
                    icon: "success"
                });
            },
            error: function(xhr, status, error) {

                console.error('AJAX Error Details');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('HTTP Status Code:', xhr.status);
                console.error('Response Text:', xhr.responseText);
                console.error('Response JSON:', xhr.responseJSON);
                console.error('Full XHR Object:', xhr);

                Swal.fire({
                    title: "Error!",
                    text: "There's an error clicking the button!",
                    icon: "error"
                });
                console.error('AJAX Error:', error);
            }

        });

    });

    $(document).off("click", "#testbutton2").on("click", "#testbutton2", function(e) {

        $.ajax({
            url: "backend/bk_test.php",
            method: "POST",
            data: {
                request: "fetchbutton"
            },
            success: function(dataresult) {

                Swal.fire({
                    title: "Success!",
                    text: "The button has been clicked!",
                    icon: "success"
                });

            },
            error: function(xhr, status, error) {
                console.error('AJAX Error Details');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('HTTP Status Code:', xhr.status);
                console.error('Response Text:', xhr.responseText);
                console.error('Response JSON:', xhr.responseJSON);
                console.error('Full XHR Object:', xhr);
            }
        });

    });
</script>
