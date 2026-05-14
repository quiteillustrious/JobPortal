const UserInfo = [];

//Publication Close Checker
$(document).ready(function() {

    let isRunning = false;

    function checkAutoClose() {

        if (isRunning) return;
        isRunning = true;

        $.ajax({
            url: "backend/bk_hrclosepublication.php",
            method: "POST",
            data: {
                request: "closepublications"
            },
            dataType: "json",

            success: function(response) {

                if (response.updated > 0) {
                Swal.fire({
                    toast: true,
                    position: "top-end",
                    icon: "info",
                    title: "A publication has ended.",
                    showConfirmButton: false,
                    timer: 3000
                });
                }

            },

            error: function(xhr, status, error) {
                console.log("Auto-close error:", error);
            },

            complete: function() {
                isRunning = false;
            }
        });
    }

    checkAutoClose();

    setInterval(checkAutoClose, 60000);

});
//Publication Close Checker End

//Modal Stacking
$(document).on('show.bs.modal', '.modal', function () {

  var zIndex = 1050 + (10 * $('.modal:visible').length);
  $(this).css('z-index', zIndex);

  setTimeout(function () {
    $('.modal-backdrop').not('.modal-stack')
      .css('z-index', zIndex - 1)
      .addClass('modal-stack');
  }, 0);

});
//Modal Stacking -- End

//Notifications
let previousNotifCount = 0;
let previousMessageCount = 0;
function fetchdata() {
    var user_id = UserInfo["UserID"];
    var user_email = UserInfo["EmailAddress"];
    var user_office = UserInfo["Office_id"];
    $.ajax({
        url: "backend/bk_notifications.php",
        method: "POST",
        data: {
            userid: user_id,
            request: 'fetchnotif'
        },
        success: function(dataResult) {
            const currentCount = parseInt(dataResult);
            if (isNaN(currentCount)) return;
            if (currentCount > previousNotifCount) {
                document.getElementById('notif-sound').play();
				Swal.fire({
				  position: "top",
				  icon: "warning",
				  title: "You have "+dataResult+" notification/s.",
				  background: '#f7f7f7',
				  showConfirmButton: false,
				  toast: true,
				  width: 400,
				  padding: "2em",
				  timer: 5000,
				  timerProgressBar: true,
				});
            }
            $("#notifcount").html(currentCount);
            previousNotifCount = currentCount;
        }
    });
}
/*
function fetchmessagecount() {
    var user_id = UserInfo["UserID"];
    var user_email = UserInfo["EmailAddress"];
    var user_office = UserInfo["Office_id"];
    $.ajax({
        url: "backend/bk_fetchnotif.php",
        method: "POST",
        data: {
            user_id: user_id,
            user_email: user_email,
            user_office: user_office,
            request: 'fetchmess'
        },
        success: function(dataResult) {
            const currentMessCount = parseInt(dataResult);
            if (isNaN(currentMessCount)) return;
            if (currentMessCount > previousMessageCount) {
                document.getElementById('notif-sound').play();
				Swal.fire({
				  position: "top",
				  icon: "warning",
				  title: "You have "+dataResult+" message/s.",
				  background: '#f7f7f7',
				  showConfirmButton: false,
				  toast: true,
				  width: 400,
				  padding: "2em",
				  timer: 5000,
				  timerProgressBar: true,
				});
            }
            $("#messagecount").html(currentMessCount);
            previousMessageCount = currentMessCount;
        }
    });
}
    */
$(document).ready(function() {
    fetchdata();
 //   fetchmessagecount();
    setInterval(fetchdata, 5000);
 //   setInterval(fetchmessagecount, 5000);
});

$(document).off('click', '#notifsee').on('click', '#notifsee', function () {
    $('#notifmodal').modal('show');
    loadNotifications();
});

function loadNotifications() {
    $.ajax({
        url: "backend/bk_notifications.php",
        method: "POST",
        data: {
            userid: UserInfo["UserID"],
            request: "seenotif"
        },
        beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },
        success: function(data) {
            $("#loadingSpinner").fadeOut(200).css("display", "none");
            $("#notifcontent").html(data);
        }
    });
}

$(document).off('click', '#readnotif').on('click', '#readnotif', function () {

    var datavalue = $(this).data("datavalue");
    var targeturl = $(this).data("targeturl");
    var dataisread = $(this).data("dataisread");

    $.ajax({
        url: "backend/bk_notifications.php",
        method: "POST",
        data: {
            userid: UserInfo["UserID"],
            request: "readnotif",
            datavalue: datavalue,
            dataisread: dataisread
        },
        success: function(data) {
            $("#notifmodal").modal("hide");

                if (!targeturl.startsWith("page/") && !targeturl.startsWith("/")) {
                    targeturl = "page/" + targeturl;
                }
                $.ajax({
                    type: "POST",
                    url: targeturl,
                    data: {
                        UserID: UserInfo["UserID"]
                    },
                    beforeSend: function() {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                        },
                    success: function(dataResult) {
                        $("#loadingSpinner").fadeOut(200).css("display", "none");
                        $("#mainContent").html(dataResult);
                    },
                    error: function(xhr, status, error) {
                        $("#loadingSpinner").fadeOut(200).css("display", "none");
                        console.error("AJAX error:", error);
                        $("#mainContent").html("<p class='p-3'>Error loading page: " + error + "</p>");
                    }
                });
        }
    });

});
//Notifications -- End

//Autocall Function
function autocall(page = "", id = "", request = "") {
    $.ajax({
        type: "POST",
        url: "page/" + page + ".php",
        data: { request, id },
        beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },
        success: function(dataResult) {
			$("#loadingSpinner").fadeOut(200).css("display", "none");
            $("#container").html(dataResult);
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200).css("display", "none");
            console.error("Error:", error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
    });
}
//Autocall Function End

//DivLoader Start
function DivLoader(divId, url, data = {}, callback) {
    var $div = $('#' + divId);

    $div.html('<div class="text-center py-3">Loading...</div>');

    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: function(response) {
            $div.html(response);
            if (callback && typeof callback === 'function') {
                callback(response);
            }
        },
        error: function(xhr, status, error) {
            $div.html('<div class="text-danger">Failed to load content.</div>');
            console.error('AJAX Error:', status, error);
        }
    });
}
//DivLoader End

//Callpages (Sidebar)
$(document).off('click', '#callpages').on('click', '#callpages', function(e) {
    e.preventDefault();

    var pagename = $(this).attr('data-pagename');

    if (!pagename.startsWith("page/") && !pagename.startsWith("/")) {
        pagename = "page/" + pagename;
    }
    $.ajax({
        type: "POST",
        url: pagename,
		data: {
			UserID: UserInfo["UserID"]
		},
		beforeSend: function() {
          $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
            },
        success: function(dataResult) {
			$("#loadingSpinner").fadeOut(200).css("display", "none");
            $("#mainContent").html(dataResult);
        },
        error: function(xhr, status, error) {
			$("#loadingSpinner").fadeOut(200).css("display", "none");
            console.error("AJAX error:", error);
            $("#mainContent").html("<p class='p-3'>Error loading page: " + error + "</p>");
        }
    });
});
//Callpages (Sidebar) End

//Sidebar Dropdown
$(document).off('click', '#clckdropdown').on('click', '#clckdropdown', function(e) {

    var $dropdown = $(this).closest('li');
    var submenuID = $(this).attr('data-IDsubmenu');
    var $submenu = $("#" + submenuID);

    if ($dropdown.hasClass("menu-open")) {
        $submenu.stop(true,true).slideUp(200);
        $dropdown.removeClass("menu-open");
    } else {
        $submenu.stop(true,true).slideDown(200);
        $dropdown.addClass("menu-open");
    }

});
//Sidebar Dropdown End

$(document).off('click', '#btnLogin').on('click', '#btnLogin', function(e) {

    var lgtxtusername = $("#lgtxtusername").val();
    var lgtxtpassword = $("#lgtxtpassword").val();

    Swal.fire({
        title: "TAU Advisory & Data Privacy Policy",
        icon: "info",
        width: 700,
        html: `
            <div style="text-align:left; max-height:300px; overflow-y:auto; font-size:14px;">
                <p class="font-weight-bold text-danger">Important Advisory:</p>
                <p>
                    The Tarlac Agricultural University (TAU) highly encourages all interested and qualified applicants — including Persons with Disability (PWDs) and members of the indigenous communities, and individuals of any sexual orientation and gender identities and/or expression, civil status, religion, and political affiliation — to apply for the vacant positions. All applicants shall be assessed solely based on their qualifications, competence, and merit.
                </p>

                <p>
                    TAU upholds the Equal Opportunities for Employment (EOP) principle, ensuring a fair, transparent, and merit-based recruitment, selection, and placement (RSP) process without discrimination.
                </p>

                <p>
                    The duration of the RSP process may vary depending on factors such as the availability of the Human Resource Merit Promotion and Selection Board (HRMPSB), preparation of examination and evaluation materials, operational requirements of the TAU, and the volume of applications requiring individual assessment.
                </p>

                <p>
                    This guide aims to set clear expectations and provide applicants with a better understanding of the process, ensuring a smooth and meaningful recruitment experience.
                </p>

                <p class="font-weight-bold text-danger">TAU's Data Privacy Policy:</p>

                <p>
                    I, a Filipino of legal age, hereby authorize the Tarlac Agricultural University (TAU) to conduct a background verification of my personal information, school, and employment records. I confirm that I understand that my personal information is protected by Republic Act No. 10173, also known as the “Data Privacy Act of 2012”.
                </p>

                <p>
                    I authorize all individuals who may have information relevant to my application to disclose it to the TAU and release the individuals concerned from liability for such disclosure. I acknowledge that the TAU may validate information provided in my application.
                </p>

                <p>
                    I am aware that I have the right to revoke this consent and request TAU to cease processing or disclosing my personal data. I understand, however, that withdrawal of consent will prevent TAU from proceeding with my job application.
                </p>

                <p>
                    By signing this form:
                    <ul>
                        <li>I confirm that I have read, understood, and accepted the terms and conditions outlined herein.</li>
                        <li>I acknowledge that my data will only be shared by authorized personnel of the Agency.</li>
                        <li>I understand that any requests to withdraw, access, or correct my data must be made in writing through the Human Resource Management Office at tau_hrmo-rsp@tau.edu.ph.</li>
                    </ul>
                </p>

                <p>
                    The information provided will be used solely for internal communication and evaluation purposes, in accordance with Republic Act No. 10173 (Data Privacy Act of 2012).
                </p>

                <p>
                    I hereby acknowledge that I have been fully informed of the foregoing and voluntarily give my consent to the collection and processing of my personal data by Tarlac Agricultural University (TAU).
                </p>

            </div>

            <div style="margin-top:15px; text-align:left;">
                <input type="checkbox" id="consentCheckbox">
                <label for="consentCheckbox"> I have read and agree to the TAU Advisory and Data Privacy Policy</label>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: "I Agree",
        cancelButtonText: "Cancel",
        focusConfirm: false,
        scrollbarPadding: false,

        preConfirm: () => {
            const isChecked = document.getElementById('consentCheckbox').checked;

            if (!isChecked) {
                Swal.showValidationMessage("You must agree before proceeding.");
                return false;
            }

            return true;
        }

    }).then((consentResult) => {

        if (!consentResult.isConfirmed) {
            return;
        }

        $.ajax({
            type: "POST",
            url: "backend/bk_login.php",
            data: { request: "verifyLogin", lgtxtusername, lgtxtpassword },
            beforeSend: function() {
                $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
            },
            success: function(dataResults) {

                var dataRes = JSON.parse(dataResults);

                if (dataRes.status === "Registered") {

                    UserInfo["UserID"] = dataRes.UserID;
                    UserInfo["RID"] = dataRes.RID;
                    UserInfo["EmailAddress"] = dataRes.EmailAddress;
                    UserInfo["UnitID"] = dataRes.UnitID;
                    UserInfo["Username"] = dataRes.Username;
                    UserInfo["Password"] = dataRes.Password;
                    UserInfo["LastName"] = dataRes.LastName;
                    UserInfo["FirstName"] = dataRes.FirstName;
                    UserInfo["Cypher"] = dataRes.Cypher;

                    $.ajax({
                        type: "POST",
                        url: "page/dashboard.php",
                        data: {
                            UserID: UserInfo["UserID"],
                            RID: UserInfo["RID"],
                            EmailAddress: UserInfo["EmailAddress"],
                            UnitID: UserInfo["UnitID"],
                            Username: UserInfo["Username"],
                            Password: UserInfo["Password"],
                            LastName: UserInfo["LastName"],
                            FirstName: UserInfo["FirstName"],
                            Cypher: UserInfo["Cypher"]
                        },
                        success: function(dataResult) {

                            $("#loadingSpinner").fadeOut(200, function() {
                                $("#loadingSpinner").css("display", "none");
                            });

                            Swal.fire({
                              title: "Verified!",
                              text: "You have been logged in.",
                              icon: "success",
                              confirmButtonText: "Cool!",
                              scrollbarPadding: false,
                              timer: 2500,
                              timerProgressBar: true
                            })
                            .then(() => {

                                $("#container").html(dataResult);
                                $('#welcomemodal').modal('show');

                            });
                        }
                    });

                } else if (dataRes.status === "unrecognized") {

                    $("#loadingSpinner").fadeOut(200).css("display", "none");

                    Swal.fire({
                      title: "Not Recognized!",
                      text: dataRes.message,
                      icon: "error",
                      scrollbarPadding: false,
                      confirmButtonText: "Close",
                    });

                }
            },
            error: function(xhr, status, error) {
                $("#loadingSpinner").fadeOut(200).css("display", "none");
            }
        });

    });
});

$(document).on('keydown', '#lgtxtusername, #lgtxtpassword', function(e) {
    if (e.key === "Enter") {
        e.preventDefault();
        $('#btnLogin').click();
    }
});

$(document).off("click", "#sendOTP").on("click", "#sendOTP", function(e) {

var fields = {};
for (let i = 1; i <= 7; i++) {
	fields[`field${i}`] = $(`#field${i}`).val().trim();
}

var required = [
	{ id: "field1", label: "Username" },
	{ id: "field2", label: "Password" },
	{ id: "field3", label: "Password Confirmation" },
	{ id: "field4", label: "Last Name" },
	{ id: "field5", label: "First Name" },
	{ id: "field6", label: "Email Address" }
];

var emptyField = required.find(f => !fields[f.id]);

if (emptyField) {
	Swal.fire({
		title: "Missing " + emptyField.label + "!",
		text: "Please enter your " + emptyField.label.toLowerCase() + " first.",
		icon: "error",
		showConfirmButton: false,
		timer: 2000,
		timerProgressBar: true,
		scrollbarPadding: false
	});
	return;
}

if (fields["field2"] != fields["field3"]){
	Swal.fire({
		title: "Passwords don't match!",
		text: "Please enter matching passwords.",
		icon: "error",
		showConfirmButton: false,
		timer: 2000,
		timerProgressBar: true,
		scrollbarPadding: false
	});
	return;
}

var email = fields["field6"];
var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

if (!emailPattern.test(email)) {
	Swal.fire({
		title: "Invalid Email!",
		text: "Please enter a valid email address.",
		icon: "error",
		showConfirmButton: false,
		timer: 2000,
		timerProgressBar: true,
		scrollbarPadding: false
	});
	return;
}

	 $.ajax({
		url: "backend/bk_registeraccount.php",
		method: "POST",
		data: {
			request: "sendotp",
			fields: fields
		},
		dataType: "json",

		beforeSend: function() {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},

		success: function(dataResult) {

			$("#loadingSpinner").fadeOut(200, function() {
				$("#loadingSpinner").css("display", "none");
			});

			if (dataResult.status === "success") {
				Swal.fire({
					title: "OTP Sent!",
					text: dataResult.message,
					icon: "success",
					timer: 2000,
					timerProgressBar: true,
					showConfirmButton: false,
					scrollbarPadding: false
				});
			} else {
				Swal.fire({
					title: "Error!",
					text: dataResult.message,
					icon: "error",
					scrollbarPadding: false
				});
			}

		},

		error: function(xhr, status, error) {

			$("#loadingSpinner").fadeOut(200, function() {
				$("#loadingSpinner").css("display", "none");
			});

			Swal.fire({
				title: "Error!",
				text: "Failed to send OTP.",
				icon: "error",
				showConfirmButton: false,
				scrollbarPadding: false
			});

			console.log(error);
		}

	});

});

$(document).off("click", "#finalregistration").on("click", "#finalregistration", function(e) {
e.preventDefault();
	var otp = $("#field7").val();
	var email = $("#field6").val();

	 $.ajax({
		url: "backend/bk_registeraccount.php",
		method: "POST",
		data: {
			request: "finalregistration",
			otp: otp,
			email: email
		},
		dataType: "json",

		beforeSend: function() {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},

		success: function(dataResult) {

			$("#loadingSpinner").fadeOut(200, function() {
				$("#loadingSpinner").css("display", "none");
			});

			if (dataResult.status === "success") {
				Swal.fire({
					title: "Success!",
					text: dataResult.message,
					icon: "success",
					timer: 2000,
					timerProgressBar: true,
					showConfirmButton: false,
					scrollbarPadding: false
				});
				$('#addeditmodal').modal('hide');
			} else {
				Swal.fire({
					title: "Error!",
					text: dataResult.message,
					icon: "error",
					scrollbarPadding: false
				});
			}

		},

		error: function(xhr, status, error) {

			$("#loadingSpinner").fadeOut(200, function() {
				$("#loadingSpinner").css("display", "none");
			});

			Swal.fire({
				title: "Error!",
				text: "Failed to Register.",
				icon: "error",
				showConfirmButton: false,
				scrollbarPadding: false
			});

			console.log(error);
		}

	});

});

//Log-in End

//Admin Priv Password
function AdminPriv() {

  if (prompt() != UserInfo["Cypher"]) {
		Swal.fire({
		  title: "Nope!",
		  text: "You've entered the wrong password...",
		  icon: "error",
		  scrollbarPadding: false
		});
    die();
  }
}

//Admin Priv Password End

//Dark Mode Toggle
$(document).off('click', '#customSwitch1').on('click', '#customSwitch1', function(e) {
    var isDark = $("#container").hasClass("dark-mode");

    $("#container").attr("class", isDark ? "sidebar-mini layout-fixed control-sidebar-slide-open" : "sidebar-mini layout-fixed control-sidebar-slide-open dark-mode");
    $("#navbardarkmode").attr("class", isDark ? "main-header navbar navbar-expand navbar-light" : "main-header navbar navbar-expand navbar-dark");
    $("#sidebardarkmode").attr("class", isDark ? "main-sidebar sidebar-light-success elevation-4" : "main-sidebar sidebar-dark-success elevation-4");
});
//Dark Mode Toggle End

//Toggle Password
$(document).off('click', '#showPasswordCheckbox').on('click', '#showPasswordCheckbox', function(e) {

    $('#lgtxtpassword').attr('type', $(this).is(':checked') ? 'text' : 'password');

});

class PasswordToggler {

    constructor() {
        $('[data-toggle="tooltip"]').tooltip();
    }

    toggle(id) {
        const $input = $(`#password_${id}`);
        const $button = $(`#btn_${id}`);
        const $icon = $(`#eye_${id}`);
        const password = $input.data('password');

        const isMasked = $input.attr('type') === 'password';

        $input

            .attr('type', isMasked ? 'text' : 'password')
            .val(isMasked ? password : '$input');

        $icon
            .toggleClass('fa-eye', !isMasked)
            .toggleClass('fa-eye-slash', isMasked);

        $button
            .attr('title', isMasked ? 'Hide Password' : 'Show Password')
            .tooltip('dispose')
            .tooltip();
    }
}
//Toggle Password End

//Table Loader Array
class TableLoader {
    static load({ tableId, url, request, onSuccess = null }) {
        $.post(url, request, function(data) {
            if (typeof onSuccess === "function") {
                onSuccess(data, tableId);
            } else {
                $(tableId).html(data);
            }
        }).fail(function(xhr, status, error) {
            console.error("Failed to load table:", error);
        });
    }
}
//Table Loader Array End

//Add Modal
$(document).off('click', '#add_data').on('click', '#add_data', function(e) {

	var fetchdata = $(this);
		console.log(fetchdata);
		$.ajax({
			url:	fetchdata.data('backendurl'),
			method:	"POST",
			data:{
					request: fetchdata.data('backendrequest'),
				},

			beforeSend: function(xhr) {
				$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
			},

			success:function(dataResult){
					$("#loadingSpinner").fadeOut(200, function() {
						$("#loadingSpinner").css("display", "none");
					});

				$('#addeditlabel').html(fetchdata.data('openmodallabel'));
				$(fetchdata.data('openmodalbody')).html(dataResult);
				$(fetchdata.data('openmodal')).modal('show');

			},

			error: function(xhr, status, error) {
					$("#loadingSpinner").fadeOut(200, function() {
						$("#loadingSpinner").css("display", "none");
					});
				console.log("Error occurred:", error);
			}

		});
});
//Add Modal End

//Edit Modal
$(document).off('click', '#edit_data').on('click', '#edit_data', function(e) {

	var fetchdata = $(this);

	$.ajax({
			url:	fetchdata.data('backendurl'),
			method:	"POST",
			data:{
					datavalue: fetchdata.data('datavalue'),
					request: fetchdata.data('backendrequest'),
				},
			beforeSend: function(xhr) {
				$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
			},

			success:function(dataResult){
					$("#loadingSpinner").fadeOut(200, function() {
						$("#loadingSpinner").css("display", "none");
					});
					$(fetchdata.data('openmodalbody')).html(dataResult);
					$('#addeditlabel').html(fetchdata.data('openmodallabel'));
					$(fetchdata.data('openmodal')).modal('show');
			}
	});
});
//Edit Modal End

//Delete Modal
$(document).off('click', '#delete_data').on('click', '#delete_data', function(e) {

	var fetchdata = $(this);

	$.ajax({
			url:	fetchdata.data('backendurl'),
			method:	"POST",
			data:{
					datavalue: fetchdata.data('datavalue'),
					request: fetchdata.data('backendrequest'),
				},
			beforeSend: function(xhr) {
				$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
			},

			success:function(dataResult){
					$("#loadingSpinner").fadeOut(200, function() {
						$("#loadingSpinner").css("display", "none");
					});
					$(fetchdata.data('openmodalbody')).html(dataResult);
					$('#addeditlabel').html(fetchdata.data('openmodallabel'));
					$(fetchdata.data('openmodal')).modal('show');
			}
	});
});
//Delete Modal End

//Save Data
$(document).off('click', '#save_data').on('click', '#save_data', function(e) {

	var fetchdata = $(this);

	var fields = {};

	for (let i = 1; i <= $('.field-input').length; i++) {
		let value = $(`#field${i}`).val();
		fields[`field${i}`] = value;
	}

		$.ajax({
			url:	fetchdata.data('backendurl'),
			method:	"POST",
			data:{
					fields: fields,
					datavalue: fetchdata.data('datavalue'),
					operator: fetchdata.data('operator'),
					logslocation: fetchdata.data('logslocation'),
					request: fetchdata.data('backendrequest'),
					userid: UserInfo['UserID']
				},
			beforeSend: function(xhr) {
				$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
			},

			success:function(dataResult){
				if(JSON.parse(dataResult).status == 'success'){
					$("#loadingSpinner").fadeOut(200, function() {
						$("#loadingSpinner").css("display", "none");
					});

						Swal.fire({
						  title: "Nice!",
						  text: JSON.parse(dataResult).message,
						  icon: "success",
						  confirmButtonText: "Cool!",
						  scrollbarPadding: false
						})

					$(fetchdata.data('openmodal')).modal('hide');
					TableLoader.load({
						tableId: fetchdata.data('tableid'),
						url: fetchdata.data('backendurl'),
						request: { request: fetchdata.data('tablerequest'), datavalue: fetchdata.data('datavalue') },
					});
				}

				else if (JSON.parse(dataResult).status == 'error'){
					$("#loadingSpinner").fadeOut(200, function() {
						$("#loadingSpinner").css("display", "none");
					});
						Swal.fire({
						  title: "Nope!",
						  text: JSON.parse(dataResult).message,
						  icon: "error",
						  confirmButtonText: "I see!",
						  scrollbarPadding: false
						})
				}

			},

			error: function(xhr, status, error) {
				$("#loadingSpinner").fadeOut(200, function() {
					$("#loadingSpinner").css("display", "none");
				});
				console.log("Error occurred:", error);
			}

		});
});
//Save Data End

//StatusTrigger
$(document).off('click', '#statustrigger').on('click', '#statustrigger', function(e) {

	var fetchdata = $(this);

		$.ajax({
			url:	fetchdata.data('backendurl'),
			method:	fetchdata.data('backendmethod'),
			data:{
					request: fetchdata.data('backendrequest'),
					datavalue: fetchdata.data('statusdata'),
					dataparam: fetchdata.data('statuscontent'),
					databasedir: fetchdata.data('databasedir'),
					databaseparam: fetchdata.data('databaseparam')
				},
			datatype:'json',
			beforeSend: function(xhr) {
				$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
			},

			success:function(dataResult){
					$("#loadingSpinner").fadeOut(200, function() {
						$("#loadingSpinner").css("display", "none");
					});

					Swal.fire({
					  title: "Success!",
					  text: JSON.parse(dataResult).message,
					  icon: "success",
					  scrollbarPadding: false
					});

					TableLoader.load({
						tableId: fetchdata.data('tableid'),
						url: fetchdata.data('tablebackendurl'),
						request: { request: fetchdata.data('tablerequest'), datavalue : fetchdata.data('datavalue')},
					});

			},

			error: function(xhr, status, error) {
				$("#loadingSpinner").fadeOut(200, function() {
					$("#loadingSpinner").css("display", "none");
				});

				Swal.fire({
				  title: "Error!",
				  text: "There's an error somewhere...",
				  icon: "error",
				  showConfirmButton: false,
				  scrollbarPadding: false
				});

				console.log("Error occurred:", error);
			}

		});

});
//Status Trigger End

//Role Select
$(document).off('change', '#topselection').on('change', '#topselection', function(e) {

	var fetchdata = $(this);

		$.ajax({
			url:	fetchdata.data('backendurl'),
			method:	fetchdata.data('backendmethod'),
			data:{
					request: fetchdata.data('backendrequest'),
					datavalue: fetchdata.val(),
				},

			beforeSend: function(xhr) {
				$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
			},

			success:function(dataResult){
					$("#loadingSpinner").fadeOut(200, function() {
						$("#loadingSpinner").css("display", "none");
					});

					$(fetchdata.data('backendtarget')).html(dataResult);

			},

			error: function(xhr, status, error) {
				$("#loadingSpinner").fadeOut(200, function() {
					$("#loadingSpinner").css("display", "none");
				});
				console.log("Error occurred:", error);
			}

		});

});
//Role Select End

//Query
$(document).off('click', '#querybutton').on('click', '#querybutton', function(e) {

	var fetchdata = $(this);

		$.ajax({
			url:	fetchdata.data('backendurl'),
			method:	fetchdata.data('backendmethod'),
			data:{
					request: fetchdata.data('backendrequest'),
					input: $('#queryinput').val(),
				},

			beforeSend: function(xhr) {
				$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
			},

			success:function(dataResult){
					$("#loadingSpinner").fadeOut(200, function() {
						$("#loadingSpinner").css("display", "none");
					});

					$(fetchdata.data('backendtarget')).html(dataResult);

			},

			error: function(xhr, status, error) {
				$("#loadingSpinner").fadeOut(200, function() {
					$("#loadingSpinner").css("display", "none");
				});
				console.log("Error occurred:", error);
			}

		});

});
//Query End

//Profile Management -- User
$(document).off('click', '#save_personaldetails').on('click', '#save_personaldetails', function(e) {

	var data = {};

	$('#personaldetailsdiv')
	.find('input, select, textarea')
		.each(function () {
			var id = this.id;

			if (id) {
				data[id] = $(this).val();
			}
		});

		var email = (data['email_address'] || '').trim();
		var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

		if (!emailRegex.test(email)) {
			Swal.fire({
				title: "Invalid Email!",
				text: "Please enter a valid email address.",
				icon: "error",
				confirmButtonText: "OK",
				scrollbarPadding: false
			});
			return;
		}

		$.ajax({
			url:	"backend/bk_profilemanagement.php",
			method:	"POST",
			data:{
					request: "savepersonaldetails",
					fields: data,
					datavalue: $('#save_personaldetails').data('datavalue')
				},
			dataType: "json",
			beforeSend: function(xhr) {
				$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
			},

			success:function(dataResult){
				if(dataResult.status == 'success'){
					$("#loadingSpinner").fadeOut(200, function() {
						$("#loadingSpinner").css("display", "none");
					});

					Swal.fire({
						title: "Nice!",
						text: dataResult.message,
						icon: "success",
						confirmButtonText: "Cool!",
						scrollbarPadding: false
					})
				}

				else if (dataResult.status == 'error'){
					$("#loadingSpinner").fadeOut(200, function() {
						$("#loadingSpinner").css("display", "none");
					});
						Swal.fire({
						  title: "Oops!",
						  text: dataResult.message,
						  icon: "error",
						  confirmButtonText: "I see!",
						  scrollbarPadding: false
						})
				}

			},

			error: function(xhr, status, error) {
				$("#loadingSpinner").fadeOut(200, function() {
					$("#loadingSpinner").css("display", "none");
				});
				console.log("Error occurred:", error);
			}

		});
});
//Profile Management -- User End
$(document).off('click', '#save_educationdetails').on('click', '#save_educationdetails', function(e){
    e.preventDefault();

    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to save this education record?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, save it!',
        cancelButtonText: 'Cancel',
        scrollbarPadding: false
    }).then((result) => {
        if(result.isConfirmed){
            var formData = new FormData();

            formData.append('request', 'saveeducation');
            formData.append('userid', UserInfo["UserID"]);

            $('#educationdiv').find('input, select, textarea').each(function(){
                var id = $(this).attr('id');
                if(id && $(this).attr('type') !== 'file'){
                    formData.append(id, $(this).val());
                }
            });

            $('#award_container .form-group').each(function(index){
                var awardSelect = $(this).find('select');
                var awardFile   = $(this).find('input[type="file"]');

                var award_id   = awardSelect.val();
                var award_desc = awardSelect.find('option:selected').text();

                formData.append(`awards[${index}][id]`, award_id);

                if(awardFile[0].files.length > 0){
                    var file = awardFile[0].files[0];
                    var ext = file.name.split('.').pop();
                    var sanitizedDesc = award_desc.replace(/[^a-z0-9]/gi, '_');
                    var newFileName = `${award_id}_${sanitizedDesc}.${ext}`;
                    var renamedFile = new File([file], newFileName, {type: file.type});
                    formData.append(`awards[${index}][file]`, renamedFile);
                }
            });

            $.ajax({
                url: "backend/bk_profilemanagement.php",
                method: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json",
                beforeSend: function() {
                    $("#loadingSpinner").fadeIn(200);
                },
                success: function(dataResult){
                    $("#loadingSpinner").fadeOut(200);
                    if(dataResult.status === 'success'){
                        Swal.fire("Nice!", dataResult.message, "success");
                        DivLoader(
                            'educationbody',
                            'backend/bk_profilemanagement.php',
                            { request: 'vieweducation', userid: UserInfo['UserID'] }
                        );
                        $('#addeditmodal').modal('hide');
                    } else {
                        Swal.fire("Oops!", dataResult.message, "error");
                    }
                },
                error: function(xhr, status, error){
                    $("#loadingSpinner").fadeOut(200);
                    console.error(error);
                }
            });
        }
    });
});
//Profile Management -- End

//Delete Education Record
$(document).off('click', '#delete_education').on('click', '#delete_education', function(e) {

    var fetchdata = $(this);

    Swal.fire({
        title: "Are you sure?",
        text: "This will permanently delete this education record.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        scrollbarPadding: false
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: fetchdata.data('backendurl'),
                method: "POST",
                data: {
                    datavalue: fetchdata.data('datavalue'),
                    request: fetchdata.data('backendrequest'),
                },
				dataType: "json",

                beforeSend: function(xhr) {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                },

                success: function(dataResult){

                    $("#loadingSpinner").fadeOut(200, function(){
                        $(this).css("display", "none");
                    });

                    if(dataResult.status === 'success'){
                        Swal.fire({
                            title: "Deleted!",
                            text: dataResult.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            scrollbarPadding: false
                        });

                        DivLoader(
                            'educationbody',
                            'backend/bk_profilemanagement.php',
                            { request: 'vieweducation', userid: UserInfo['UserID'] }
                        );

                    } else {
                        Swal.fire({
                            title: "Oops!",
                            text: dataResult.message,
                            icon: "error",
                            confirmButtonText: "I see!",
                            scrollbarPadding: false
                        });
                    }
                },

                error: function(xhr, status, error){
                    $("#loadingSpinner").fadeOut(200, function(){
                        $(this).css("display", "none");
                    });
                    console.error("Error occurred:", error);
                }
            });

        }
    });
});
//Delete Education Record End

//Save Eligibility Details
$(document).off('click', '#save_eligibilitydetails').on('click', '#save_eligibilitydetails', function(e){
    e.preventDefault();

    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to save this eligibility record?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, save it!',
        cancelButtonText: 'Cancel',
        scrollbarPadding: false
    }).then((result) => {
        if(result.isConfirmed){

            var formData = new FormData();

            formData.append('request', 'saveeligibility');
            formData.append('userid', UserInfo["UserID"]);

            $('#eligibilitydiv').find('input, select, textarea').each(function(){
                var id = $(this).attr('id');

                if(id && $(this).attr('type') !== 'file'){
                    formData.append(id, $(this).val());
                }
            });

            var fileInput = $('#proof_eligibility')[0];
            if(fileInput.files.length > 0){
                var file = fileInput.files[0];

                var ext = file.name.split('.').pop();
                var newFileName = "eligibility_proof_" + Date.now() + "." + ext;
                var renamedFile = new File([file], newFileName, {type: file.type});

                formData.append('proof_eligibility', renamedFile);
            }

            $.ajax({
                url: "backend/bk_profilemanagement.php",
                method: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json",
                beforeSend: function() {
                    $("#loadingSpinner").fadeIn(200);
                },
                success: function(dataResult){
                    $("#loadingSpinner").fadeOut(200);

                    if(dataResult.status === 'success'){
                        Swal.fire({
                            title: "Nice!",
                            text: dataResult.message,
                            icon: "success"
                        });

                        DivLoader(
                            'eligibilitybody',
                            'backend/bk_profilemanagement.php',
                            { request: 'vieweligibility', userid: UserInfo['UserID'] }
                        );

                        $('#addeditmodal').modal('hide');

                    } else {
                        Swal.fire("Oops!", dataResult.message, "error");
                    }
                },
                error: function(xhr, status, error){
                    $("#loadingSpinner").fadeOut(200);
                    console.error(error);

                    Swal.fire("Error!", "Something went wrong.", "error");
                }
            });
        }
    });
});
//Save Eligibility Details End

//Delete Eligibility Record
$(document).off('click', '#delete_eligibility').on('click', '#delete_eligibility', function(e) {

    var fetchdata = $(this);

    Swal.fire({
        title: "Are you sure?",
        text: "This will permanently delete this eligibility record.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        scrollbarPadding: false
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: fetchdata.data('backendurl'),
                method: "POST",
                data: {
                    datavalue: fetchdata.data('datavalue'),
                    request: fetchdata.data('backendrequest'),
                },
				dataType: "json",

                beforeSend: function(xhr) {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                },

                success: function(dataResult){

                    $("#loadingSpinner").fadeOut(200, function(){
                        $(this).css("display", "none");
                    });

                    if(dataResult.status === 'success'){
                        Swal.fire({
                            title: "Deleted!",
                            text: dataResult.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            scrollbarPadding: false
                        });

                        DivLoader(
                            'eligibilitybody',
                            'backend/bk_profilemanagement.php',
                            { request: 'vieweligibility', userid: UserInfo['UserID'] }
                        );

                    } else {
                        Swal.fire({
                            title: "Oops!",
                            text: dataResult.message,
                            icon: "error",
                            confirmButtonText: "I see!",
                            scrollbarPadding: false
                        });
                    }
                },

                error: function(xhr, status, error){
                    $("#loadingSpinner").fadeOut(200, function(){
                        $(this).css("display", "none");
                    });
                    console.error("Error occurred:", error);
                }
            });

        }
    });
});
//Delete Eligibility Record End

//Save Work Experience Details
$(document).off('click', '#save_workexperiencedetails').on('click', '#save_workexperiencedetails', function(e){
    e.preventDefault();

    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to save this work experience record?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, save it!',
        cancelButtonText: 'Cancel',
        scrollbarPadding: false
    }).then((result) => {
        if(result.isConfirmed){

            var formData = new FormData();

            formData.append('request', 'saveworkexperience');
            formData.append('userid', UserInfo["UserID"]);

            $('#workexperiencediv').find('input, select, textarea').each(function(){
                var id = $(this).attr('id');
                if(id && $(this).attr('type') !== 'file'){
                    formData.append(id, $(this).val());
                }
            });

            var files = ['certificate_employment', 'service_record'];
            files.forEach(function(fileId){
                var fileInput = $('#' + fileId)[0];
                if(fileInput.files.length > 0){
                    var file = fileInput.files[0];
                    var ext = file.name.split('.').pop();
                    var newFileName = fileId + "_" + Date.now() + "." + ext;
                    var renamedFile = new File([file], newFileName, {type: file.type});
                    formData.append(fileId, renamedFile);
                }
            });

            $.ajax({
                url: "backend/bk_profilemanagement.php",
                method: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json",
                beforeSend: function() {
                    $("#loadingSpinner").fadeIn(200);
                },
                success: function(dataResult){
                    $("#loadingSpinner").fadeOut(200);

                    if(dataResult.status === 'success'){
                        Swal.fire({
                            title: "Nice!",
                            text: dataResult.message,
                            icon: "success"
                        });

                        DivLoader(
                            'workexperiencebody',
                            'backend/bk_profilemanagement.php',
                            { request: 'viewworkexperience', userid: UserInfo['UserID'] }
                        );

                        $('#addeditmodal').modal('hide');

                    } else {
                        Swal.fire("Oops!", dataResult.message, "error");
                    }
                },
                error: function(xhr, status, error){
                    $("#loadingSpinner").fadeOut(200);
                    console.error(error);

                    Swal.fire("Error!", "Something went wrong.", "error");
                }
            });
        }
    });
});
//Save Work Experience Details -- End

//Delete Work Experience Record
$(document).off('click', '#delete_workexperience').on('click', '#delete_workexperience', function(e) {

    var fetchdata = $(this);

    Swal.fire({
        title: "Are you sure?",
        text: "This will permanently delete this work experience record.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        scrollbarPadding: false
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: fetchdata.data('backendurl'),
                method: "POST",
                data: {
                    datavalue: fetchdata.data('datavalue'),
                    request: fetchdata.data('backendrequest'),
                },
				dataType: "json",

                beforeSend: function(xhr) {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                },

                success: function(dataResult){

                    $("#loadingSpinner").fadeOut(200, function(){
                        $(this).css("display", "none");
                    });

                    if(dataResult.status === 'success'){
                        Swal.fire({
                            title: "Deleted!",
                            text: dataResult.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            scrollbarPadding: false
                        });

                        DivLoader(
                            'workexperiencebody',
                            'backend/bk_profilemanagement.php',
                            { request: 'viewworkexperience', userid: UserInfo['UserID'] }
                        );

                    } else {
                        Swal.fire({
                            title: "Oops!",
                            text: dataResult.message,
                            icon: "error",
                            confirmButtonText: "I see!",
                            scrollbarPadding: false
                        });
                    }
                },

                error: function(xhr, status, error){
                    $("#loadingSpinner").fadeOut(200, function(){
                        $(this).css("display", "none");
                    });
                    console.error("Error occurred:", error);
                }
            });

        }
    });
});
//Delete Work Experience Record End

//Save Voluntary Work Details
$(document).off('click', '#save_voluntaryworkdetails').on('click', '#save_voluntaryworkdetails', function(e){
    e.preventDefault();

    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to save this voluntary work record?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, save it!',
        cancelButtonText: 'Cancel',
        scrollbarPadding: false
    }).then((result) => {
        if(result.isConfirmed){

            var formData = new FormData();

            formData.append('request', 'savevoluntarywork');
            formData.append('userid', UserInfo["UserID"]);

            $('#voluntaryworkdiv').find('input, select, textarea').each(function(){
                var id = $(this).attr('id');
                if(id && $(this).attr('type') !== 'file'){
                    formData.append(id, $(this).val());
                }
            });

            var fileInput = $('#org_document')[0];
            if(fileInput.files.length > 0){
                var file = fileInput.files[0];
                var ext = file.name.split('.').pop();
                var newFileName = "org_document_" + Date.now() + "." + ext;
                var renamedFile = new File([file], newFileName, {type: file.type});
                formData.append('org_document', renamedFile);
            }

            $.ajax({
                url: "backend/bk_profilemanagement.php",
                method: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json",
                beforeSend: function() {
                    $("#loadingSpinner").fadeIn(200);
                },
                success: function(dataResult){
                    $("#loadingSpinner").fadeOut(200);

                    if(dataResult.status === 'success'){
                        Swal.fire({
                            title: "Nice!",
                            text: dataResult.message,
                            icon: "success"
                        });

                        DivLoader(
                            'voluntarybody',
                            'backend/bk_profilemanagement.php',
                            { request: 'viewvoluntarywork', userid: UserInfo['UserID'] }
                        );

                        $('#addeditmodal').modal('hide');

                    } else {
                        Swal.fire("Oops!", dataResult.message, "error");
                    }
                },
                error: function(xhr, status, error){
                    $("#loadingSpinner").fadeOut(200);
                    console.error(error);

                    Swal.fire("Error!", "Something went wrong.", "error");
                }
            });
        }
    });
});
//Save Voluntary Work Details -- End

//Delete Voluntary Work Record
$(document).off('click', '#delete_voluntarywork').on('click', '#delete_voluntarywork', function(e) {

    var fetchdata = $(this);

    Swal.fire({
        title: "Are you sure?",
        text: "This will permanently delete this voluntary work record.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        scrollbarPadding: false
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: fetchdata.data('backendurl'),
                method: "POST",
                data: {
                    datavalue: fetchdata.data('datavalue'),
                    request: fetchdata.data('backendrequest'),
                },
				dataType: "json",

                beforeSend: function(xhr) {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                },

                success: function(dataResult){

                    $("#loadingSpinner").fadeOut(200, function(){
                        $(this).css("display", "none");
                    });

                    if(dataResult.status === 'success'){
                        Swal.fire({
                            title: "Deleted!",
                            text: dataResult.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            scrollbarPadding: false
                        });

                        DivLoader(
                            'voluntarybody',
                            'backend/bk_profilemanagement.php',
                            { request: 'viewvoluntarywork', userid: UserInfo['UserID'] }
                        );

                    } else {
                        Swal.fire({
                            title: "Oops!",
                            text: dataResult.message,
                            icon: "error",
                            confirmButtonText: "I see!",
                            scrollbarPadding: false
                        });
                    }
                },

                error: function(xhr, status, error){
                    $("#loadingSpinner").fadeOut(200, function(){
                        $(this).css("display", "none");
                    });
                    console.error("Error occurred:", error);
                }
            });

        }
    });
});
//Delete Voluntary Work Record -- End

//Save Learning and Development Details
$(document).off('click', '#save_learningdevelopmentdetails').on('click', '#save_learningdevelopmentdetails', function(e){
    e.preventDefault();

    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to save this Learning & Development record?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, save it!',
        cancelButtonText: 'Cancel',
        scrollbarPadding: false
    }).then((result) => {
        if(result.isConfirmed){

            var formData = new FormData();

            formData.append('request', 'savelearningdevelopment');
            formData.append('userid', UserInfo["UserID"]);

            $('#learningdevelopmentdiv').find('input, select, textarea').each(function(){
                var id = $(this).attr('id');
                if(id && $(this).attr('type') !== 'file'){
                    formData.append(id, $(this).val());
                }
            });

            var file1 = $('#ld_attachmentattendance')[0];
            if(file1.files.length > 0){
                var f1 = file1.files[0];
                var ext1 = f1.name.split('.').pop();
                var newFileName1 = "ld_attachmentattendance_" + Date.now() + "." + ext1;
                var renamedFile1 = new File([f1], newFileName1, {type: f1.type});
                formData.append('ld_attachmentattendance', renamedFile1);
            }

            var file2 = $('#ld_attachmentskills')[0];
            if(file2.files.length > 0){
                var f2 = file2.files[0];
                var ext2 = f2.name.split('.').pop();
                var newFileName2 = "ld_attachmentskills_" + Date.now() + "." + ext2;
                var renamedFile2 = new File([f2], newFileName2, {type: f2.type});
                formData.append('ld_attachmentskills', renamedFile2);
            }

            $.ajax({
                url: "backend/bk_profilemanagement.php",
                method: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json",
                beforeSend: function() {
                    $("#loadingSpinner").fadeIn(200);
                },
                success: function(dataResult){
                    $("#loadingSpinner").fadeOut(200);

                    if(dataResult.status === 'success'){
                        Swal.fire({
                            title: "Nice!",
                            text: dataResult.message,
                            icon: "success"
                        });

                        DivLoader(
                            'learningdevelopmentbody',
                            'backend/bk_profilemanagement.php',
                            { request: 'viewlearningdevelopment', userid: UserInfo['UserID'] }
                        );

                        $('#addeditmodal').modal('hide');

                    } else {
                        Swal.fire("Oops!", dataResult.message, "error");
                    }
                },
                error: function(xhr, status, error){
                    $("#loadingSpinner").fadeOut(200);
                    console.error(error);

                    Swal.fire("Error!", "Something went wrong.", "error");
                }
            });
        }
    });
});
//Save Learning and Development Details -- End

//Delete Learning and Development Record
$(document).off('click', '#delete_learningdevelopment').on('click', '#delete_learningdevelopment', function(e) {

    var fetchdata = $(this);

    Swal.fire({
        title: "Are you sure?",
        text: "This will permanently delete this Learning & Development record.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        scrollbarPadding: false
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: fetchdata.data('backendurl'),
                method: "POST",
                data: {
                    datavalue: fetchdata.data('datavalue'),
                    request: fetchdata.data('backendrequest'),
                },
                dataType: "json",

                beforeSend: function(xhr) {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                },

                success: function(dataResult){

                    $("#loadingSpinner").fadeOut(200, function(){
                        $(this).css("display", "none");
                    });

                    if(dataResult.status === 'success'){
                        Swal.fire({
                            title: "Deleted!",
                            text: dataResult.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            scrollbarPadding: false
                        });

                        DivLoader(
                            'learningdevelopmentbody',
                            'backend/bk_profilemanagement.php',
                            { request: 'viewlearningdevelopment', userid: UserInfo['UserID'] }
                        );

                    } else {
                        Swal.fire({
                            title: "Oops!",
                            text: dataResult.message,
                            icon: "error",
                            confirmButtonText: "I see!",
                            scrollbarPadding: false
                        });
                    }
                },

                error: function(xhr, status, error){
                    $("#loadingSpinner").fadeOut(200, function(){
                        $(this).css("display", "none");
                    });
                    console.error("Error occurred:", error);
                }
            });

        }
    });
});
//Delete Learning and Development Record -- End

//Skills Management
$(document).off('click', '#add_skill_btn').on('click', '#add_skill_btn', function(e) {

    var UserID = UserInfo['UserID'];

    $.ajax({
        url: $('#skill_input').data('backendurl'),
        method: "POST",
        data: {
            request: $('#skill_input').data('backendrequest'),
            skill_desc: $('#skill_input').val(),
            userid: UserID
        },
        dataType: "json",

        beforeSend: function(xhr) {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },

        success: function(dataResult){

            $("#loadingSpinner").fadeOut(200, function(){
                $(this).css("display", "none");
            });

            if(dataResult.status === 'success'){
                Swal.fire({
                    title: "Success!",
                    text: dataResult.message,
                    icon: "success",
                    confirmButtonText: "OK",
                    scrollbarPadding: false
                });

                DivLoader(
                    'skills_list',
                    'backend/bk_profilemanagement.php',
                    { request: 'viewskills', userid: UserInfo['UserID'] }
                );

            } else {
                Swal.fire({
                    title: "Oops!",
                    text: dataResult.message,
                    icon: "error",
                    confirmButtonText: "I see!",
                    scrollbarPadding: false
                });
            }
        },

        error: function(xhr, status, error){
            $("#loadingSpinner").fadeOut(200, function(){
                $(this).css("display", "none");
            });
            console.error("Error occurred:", error);
        }
    });

});

$(document).off('click', '#delete_skill').on('click', '#delete_skill', function(e) {

    var fetchdata = $(this);

    Swal.fire({
        title: "Are you sure?",
        text: "This will permanently delete this Skill.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        scrollbarPadding: false
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: fetchdata.data('backendurl'),
                method: "POST",
                data: {
                    datavalue: fetchdata.data('datavalue'),
                    request: fetchdata.data('backendrequest'),
                },
                dataType: "json",

                beforeSend: function(xhr) {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                },

                success: function(dataResult){

                    $("#loadingSpinner").fadeOut(200, function(){
                        $(this).css("display", "none");
                    });

                    if(dataResult.status === 'success'){
                        Swal.fire({
                            title: "Deleted!",
                            text: dataResult.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            scrollbarPadding: false
                        });

                        DivLoader(
                            'skills_list',
                            'backend/bk_profilemanagement.php',
                            { request: 'viewskills', userid: UserInfo['UserID'] }
                        );

                    } else {
                        Swal.fire({
                            title: "Oops!",
                            text: dataResult.message,
                            icon: "error",
                            confirmButtonText: "I see!",
                            scrollbarPadding: false
                        });
                    }
                },

                error: function(xhr, status, error){
                    $("#loadingSpinner").fadeOut(200, function(){
                        $(this).css("display", "none");
                    });
                    console.error("Error occurred:", error);
                }
            });

        }
    });
});
//Skills Management End

//Competencies Management
$(document).off('click', '#add_comp_btn').on('click', '#add_comp_btn', function(e) {

    var UserID = UserInfo['UserID'];

    $.ajax({
        url: $('#comp_input').data('backendurl'),
        method: "POST",
        data: {
            request: $('#comp_input').data('backendrequest'),
            comp_desc: $('#comp_input').val(),
            userid: UserID
        },
        dataType: "json",

        beforeSend: function(xhr) {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },

        success: function(dataResult){

            $("#loadingSpinner").fadeOut(200, function(){
                $(this).css("display", "none");
            });

            if(dataResult.status === 'success'){
                Swal.fire({
                    title: "Success!",
                    text: dataResult.message,
                    icon: "success",
                    confirmButtonText: "OK",
                    scrollbarPadding: false
                });

                DivLoader(
                    'comp_list',
                    'backend/bk_profilemanagement.php',
                    { request: 'viewcomp', userid: UserInfo['UserID'] }
                );

            } else {
                Swal.fire({
                    title: "Oops!",
                    text: dataResult.message,
                    icon: "error",
                    confirmButtonText: "I see!",
                    scrollbarPadding: false
                });
            }
        },

        error: function(xhr, status, error){
            $("#loadingSpinner").fadeOut(200, function(){
                $(this).css("display", "none");
            });
            console.error("Error occurred:", error);
        }
    });

});

$(document).off('click', '#delete_comp').on('click', '#delete_comp', function(e) {

    var fetchdata = $(this);

    Swal.fire({
        title: "Are you sure?",
        text: "This will permanently delete this Competency.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        scrollbarPadding: false
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: fetchdata.data('backendurl'),
                method: "POST",
                data: {
                    datavalue: fetchdata.data('datavalue'),
                    request: fetchdata.data('backendrequest'),
                },
                dataType: "json",

                beforeSend: function(xhr) {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                },

                success: function(dataResult){

                    $("#loadingSpinner").fadeOut(200, function(){
                        $(this).css("display", "none");
                    });

                    if(dataResult.status === 'success'){
                        Swal.fire({
                            title: "Deleted!",
                            text: dataResult.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            scrollbarPadding: false
                        });

                        DivLoader(
                            'comp_list',
                            'backend/bk_profilemanagement.php',
                            { request: 'viewcomp', userid: UserInfo['UserID'] }
                        );

                    } else {
                        Swal.fire({
                            title: "Oops!",
                            text: dataResult.message,
                            icon: "error",
                            confirmButtonText: "I see!",
                            scrollbarPadding: false
                        });
                    }
                },

                error: function(xhr, status, error){
                    $("#loadingSpinner").fadeOut(200, function(){
                        $(this).css("display", "none");
                    });
                    console.error("Error occurred:", error);
                }
            });

        }
    });
});
//Competencies Management -- End

//Non-Academic Distinction Management
$(document).off('click', '#add_ndr_btn').on('click', '#add_ndr_btn', function(e) {

    var UserID = UserInfo['UserID'];

    var formData = new FormData();
    formData.append("request", $('#ndr_input').data('backendrequest'));
    formData.append("ndr_input", $('#ndr_input').val());
    formData.append("userid", UserID);

    var file = $('#ndr_file')[0].files[0];
    if(file){
        formData.append("ndr_file", file);
    }

    $.ajax({
        url: $('#ndr_input').data('backendurl'),
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",

        beforeSend: function(xhr) {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },

        success: function(dataResult){

            $("#loadingSpinner").fadeOut(200, function(){
                $(this).css("display", "none");
            });

            if(dataResult.status === 'success'){
                Swal.fire({
                    title: "Success!",
                    text: dataResult.message,
                    icon: "success",
                    confirmButtonText: "OK",
                    scrollbarPadding: false
                });

                DivLoader(
                    'ndr_list',
                    'backend/bk_profilemanagement.php',
                    { request: 'viewndr', userid: UserID }
                );

                $('#ndr_input').val('');
                $('#ndr_file').val('');
                $('#ndr_file_name').text('No file selected');

            } else {
                Swal.fire({
                    title: "Oops!",
                    text: dataResult.message,
                    icon: "error",
                    confirmButtonText: "I see!",
                    scrollbarPadding: false
                });
            }
        },

        error: function(xhr, status, error){
            $("#loadingSpinner").fadeOut(200, function(){
                $(this).css("display", "none");
            });
            console.error("Error occurred:", error);
        }
    });

});

$(document).off('click', '#delete_ndr').on('click', '#delete_ndr', function(e) {

    var fetchdata = $(this);

    Swal.fire({
        title: "Are you sure?",
        text: "This will permanently delete this Non-Academic Distinction.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        scrollbarPadding: false
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: fetchdata.data('backendurl'),
                method: "POST",
                data: {
                    datavalue: fetchdata.data('datavalue'),
                    request: fetchdata.data('backendrequest'),
                },
                dataType: "json",

                beforeSend: function(xhr) {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                },

                success: function(dataResult){

                    $("#loadingSpinner").fadeOut(200, function(){
                        $(this).css("display", "none");
                    });

                    if(dataResult.status === 'success'){
                        Swal.fire({
                            title: "Deleted!",
                            text: dataResult.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            scrollbarPadding: false
                        });

                        DivLoader(
                            'ndr_list',
                            'backend/bk_profilemanagement.php',
                            { request: 'viewndr', userid: UserInfo['UserID'] }
                        );

                    } else {
                        Swal.fire({
                            title: "Oops!",
                            text: dataResult.message,
                            icon: "error",
                            confirmButtonText: "I see!",
                            scrollbarPadding: false
                        });
                    }
                },

                error: function(xhr, status, error){
                    $("#loadingSpinner").fadeOut(200, function(){
                        $(this).css("display", "none");
                    });
                    console.error("Error occurred:", error);
                }
            });

        }
    });
});
//Non-Academic Distinction Management End

//Membership in Associations / Organizations Management
$(document).off('click', '#add_membership_btn').on('click', '#add_membership_btn', function(e) {

    var UserID = UserInfo['UserID'];

    var formData = new FormData();
    formData.append("request", $('#membership_input').data('backendrequest'));
    formData.append("membership_input", $('#membership_input').val());
    formData.append("userid", UserID);

    var file = $('#membership_file')[0].files[0];
    if(file){
        formData.append("membership_file", file);
    }

    $.ajax({
        url: $('#membership_input').data('backendurl'),
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",

        beforeSend: function(){
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },

        success: function(dataResult){
            $("#loadingSpinner").fadeOut(200, function(){
                $(this).css("display", "none");
            });

            if(dataResult.status === 'success'){
                Swal.fire({
                    title: "Success!",
                    text: dataResult.message,
                    icon: "success",
                    confirmButtonText: "OK",
                    scrollbarPadding: false
                });

                DivLoader(
                    'membership_list',
                    'backend/bk_profilemanagement.php',
                    { request: 'viewmembership', userid: UserID }
                );

                $('#membership_input').val('');
                $('#membership_file').val('');
                $('#membership_file_name').text('No file selected');

            } else {
                Swal.fire({
                    title: "Oops!",
                    text: dataResult.message,
                    icon: "error",
                    confirmButtonText: "I see!",
                    scrollbarPadding: false
                });
            }
        },

        error: function(xhr, status, error){
            $("#loadingSpinner").fadeOut(200, function(){
                $(this).css("display", "none");
            });
            console.error("Error occurred:", error);
        }
    });

});

$(document).off('click', '#delete_membership').on('click', '#delete_membership', function(e) {

    var fetchdata = $(this);

    Swal.fire({
        title: "Are you sure?",
        text: "This will permanently delete this Association/Organization Membership.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        scrollbarPadding: false
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: fetchdata.data('backendurl'),
                method: "POST",
                data: {
                    datavalue: fetchdata.data('datavalue'),
                    request: fetchdata.data('backendrequest'),
                },
                dataType: "json",

                beforeSend: function(xhr) {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                },

                success: function(dataResult){

                    $("#loadingSpinner").fadeOut(200, function(){
                        $(this).css("display", "none");
                    });

                    if(dataResult.status === 'success'){
                        Swal.fire({
                            title: "Deleted!",
                            text: dataResult.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            scrollbarPadding: false
                        });

                        DivLoader(
                            'membership_list',
                            'backend/bk_profilemanagement.php',
                            { request: 'viewmembership', userid: UserInfo['UserID'] }
                        );

                    } else {
                        Swal.fire({
                            title: "Oops!",
                            text: dataResult.message,
                            icon: "error",
                            confirmButtonText: "I see!",
                            scrollbarPadding: false
                        });
                    }
                },

                error: function(xhr, status, error){
                    $("#loadingSpinner").fadeOut(200, function(){
                        $(this).css("display", "none");
                    });
                    console.error("Error occurred:", error);
                }
            });

        }
    });
});
//Membership in Associations / Organizations Management End

//Answers Management
$(document).off('click', '#save_answers').on('click', '#save_answers', function (e) {
    e.preventDefault();

    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to save your answers?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, save it!',
        cancelButtonText: 'Cancel',
        scrollbarPadding: false
    }).then((result) => {
        if (!result.isConfirmed) return;

        function getRadio(name) {
            let v = $('input[name="' + name + '"]:checked').val();
            return (v === undefined) ? '' : v;
        }

        function getVal(selector) {
            let v = $(selector).val();
            return (v === undefined || v === null) ? '' : v.trim();
        }

        let q35bInputs = $('#q35bDetails input');
        let q35b_date = q35bInputs.eq(0).val() || '';
        let q35b_status = q35bInputs.eq(1).val() || '';

        var data = {
            request: 'saveanswers',
            userid: UserInfo["UserID"],

            q34a: getRadio('q34a'),
            q34a_details: getVal('#q34aDetails input'),

            q34b: getRadio('q34b'),
            q34b_details: getVal('#q34bDetails input'),

            q35a: getRadio('q35a'),
            q35a_details: getVal('#q35aDetails input'),

            q35b: getRadio('q35b'),
            q35b_date: q35b_date,
            q35b_status: q35b_status,

            q36: getRadio('q36'),
            q36_details: getVal('#q36Details input'),

            q37: getRadio('q37'),
            q37_details: getVal('#q37Details input'),

            q38a: getRadio('q38a'),
            q38a_details: getVal('#q38aDetails input'),

            q38b: getRadio('q38b'),
            q38b_details: getVal('#q38bDetails input'),

            q39: getRadio('q39'),
            q39_details: getVal('#q39Details input'),

            q40a: getRadio('q40a'),
            q40a_details: getVal('#q40aDetails input'),

            q40b: getRadio('q40b'),
            q40b_details: getVal('#q40bDetails input'),

            q40c: getRadio('q40c'),
            q40c_details: getVal('#q40cDetails input'),

            q40d: getRadio('q40d'),
            q40e: getRadio('q40e')
        };

        $.ajax({
            url: "backend/bk_profilemanagement.php",
            method: "POST",
            data: data,
            dataType: "json",

            beforeSend: function () {
                $("#loadingSpinner").fadeIn(200);
            },

            success: function (res) {
                $("#loadingSpinner").fadeOut(200);

                if (res.status === 'success') {
                    Swal.fire("Nice!", res.message, "success");
                } else {
                    Swal.fire("Oops!", res.message, "error");
                }
            },

            error: function () {
                $("#loadingSpinner").fadeOut(200);
                Swal.fire("Error!", "Something went wrong.", "error");
            }
        });

    });
});
//Answers Management -- End

//PDS, Work Experience Sheet, Performance Rating Management
$(document).off('click', '[id^=upload_]').on('click', '[id^=upload_]', function(e){
    e.preventDefault();

    var fetchdata = $(this);

    const fileInputId = $(this).data('fileinput');
    const backendUrl = $(this).data('backendurl');
    const fileInput = $('#' + fileInputId)[0];
    const file = fileInput.files[0];

    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to save this file?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, save it!',
        cancelButtonText: 'Cancel',
        scrollbarPadding: false
    }).then((result) => {
        if(result.isConfirmed){
            var formData = new FormData();
            formData.append('request', 'savepdsfiles');
            formData.append('userid', UserInfo["UserID"]);
            formData.append('perf_rating', $('#perf_rating').val());
            formData.append('adj_rating', $('#adj_rating').val());
            formData.append(fileInputId, file);

            $.ajax({
                url: backendUrl,
                method: "POST",
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,

                beforeSend: function() {
                    $("#loadingSpinner").fadeIn(200);
                },

                success: function(dataResult){
                    $("#loadingSpinner").fadeOut(200);

                    if(dataResult.status === 'success'){
                        Swal.fire({
                            title: "Success!",
                            text: dataResult.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            scrollbarPadding: false
                        });

                        const targetDiv = $('#' + fetchdata.data('targetdiv'));
                        const viewBtn = targetDiv.find('#view_attachment');

                        if(viewBtn.length){
                            viewBtn.attr('data-datavalue', dataResult.attachid);
                            viewBtn.data('datavalue', dataResult.attachid);
                        }


                    } else {
                        Swal.fire({
                            title: "Oops!",
                            text: dataResult.message,
                            icon: "error",
                            confirmButtonText: "I see!",
                            scrollbarPadding: false
                        });
                    }
                },

                error: function(xhr){
                    $("#loadingSpinner").fadeOut(200);
                    console.log(xhr.responseText);
                    Swal.fire("Error!", "Something went wrong.", "error");
                }
            });
        }
    });
});
//PDS, Work Experience Sheet, Performance Rating Management -- End

//View Attachments
$(document).off('click', '#view_attachment').on('click', '#view_attachment', function(e) {
    var fetchdata = $(this);

    $.ajax({
        url: "backend/bk_viewattachment.php",
        method: "POST",
        data: {
            request: "viewattachment",
            datavalue: fetchdata.data("datavalue")
        },
        beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },
        success: function(dataResult) {
            $("#loadingSpinner").fadeOut(200, function() {
                $("#loadingSpinner").css("display", "none");
            });

            $('#attachmentmodallabel').html(fetchdata.data('openmodallabel'));
            $('#attachmentmodalcontent').html(dataResult);
            $('#attachmentmodal').modal('show');
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200, function() {
                $("#loadingSpinner").css("display", "none");
            });
            console.log("Error occurred:", error);
        }
    });
});
//View Attachments -- End

//View Position Details
$(document).off('click', '[id^="view_position_"]').on('click', '[id^="view_position_"]', function(e) {
    e.stopPropagation();
    var fetchdata = $(this);

    $.ajax({
        url: fetchdata.data("backendurl"),
        method: "POST",
        data: {
            request: fetchdata.data("backendrequest"),
            datavalue: fetchdata.data("datavalue")
        },
        beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },
        success: function(dataResult) {
            $("#loadingSpinner").fadeOut(200, function() {
                $("#loadingSpinner").css("display", "none");
            });

            $('#attachmentmodallabel').html(fetchdata.data('openmodallabel'));
            $('#attachmentmodalcontent').html(dataResult);
            $(fetchdata.data("openmodal")).modal('show');
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200, function() {
                $("#loadingSpinner").css("display", "none");
            });
            console.error("Error occurred:", error);
        }
    });
});
//View Position Details -- End

//Delete Position
$(document).off('click', '[id^="delete_position_"]').on('click', '[id^="delete_position_"]', function(e) {

    e.stopPropagation();

    var fetchdata = $(this);

    Swal.fire({
        title: "Are you sure?",
        text: "This will permanently delete this position.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        scrollbarPadding: false
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: fetchdata.data("backendurl"),
                method: "POST",
                data: {
                    request: fetchdata.data("backendrequest"),
                    datavalue: fetchdata.data("datavalue")
                },

                beforeSend: function() {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                },

                success: function(dataResult) {
                    $("#loadingSpinner").fadeOut(200, function() {
                        $("#loadingSpinner").css("display", "none");
                    });

                    // If your backend returns JSON, parse it
                    let res;
                    try {
                        res = typeof dataResult === "object" ? dataResult : JSON.parse(dataResult);
                    } catch (e) {
                        console.error("Invalid JSON:", dataResult);
                        Swal.fire("Error", "Invalid server response.", "error");
                        return;
                    }

                    if (res.status === 'success') {
                        Swal.fire({
                            title: "Deleted!",
                            text: res.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            scrollbarPadding: false
                        });

                        // Reload positions
                        loadPublications();

                    } else {
                        Swal.fire({
                            title: "Oops!",
                            text: res.message,
                            icon: "error",
                            confirmButtonText: "I see!",
                            scrollbarPadding: false
                        });
                    }
                },

                error: function(xhr, status, error) {
                    $("#loadingSpinner").fadeOut(200, function() {
                        $("#loadingSpinner").css("display", "none");
                    });
                    console.error("Error occurred:", error);
                }
            });

        }
    });
});
//Delete Position -- End

//Publish Publication
$(document).off('click', '#publish_publication').on('click', '#publish_publication', function(e) {

    e.stopPropagation();

    var fetchdata = $(this);

    Swal.fire({
        title: "Are you sure?",
        text: "This will publish this publication.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, publish it!",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        scrollbarPadding: false
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: fetchdata.data("backendurl"),
                method: "POST",
                data: {
                    request: fetchdata.data("backendrequest"),
                    datavalue: fetchdata.data("datavalue")
                },

                beforeSend: function() {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                },

                success: function(dataResult) {
                    $("#loadingSpinner").fadeOut(200, function() {
                        $("#loadingSpinner").css("display", "none");
                    });

                    let res;
                    try {
                        res = typeof dataResult === "object" ? dataResult : JSON.parse(dataResult);
                    } catch (e) {
                        console.error("Invalid JSON:", dataResult);
                        Swal.fire("Error", "Invalid server response.", "error");
                        return;
                    }

                    if (res.status === 'success') {
                        Swal.fire({
                            title: "Published!",
                            text: res.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            scrollbarPadding: false
                        });

                        loadPublications();

                    } else {
                        Swal.fire({
                            title: "Oops!",
                            text: res.message,
                            icon: "error",
                            confirmButtonText: "I see!",
                            scrollbarPadding: false
                        });
                    }
                },

                error: function(xhr, status, error) {
                    $("#loadingSpinner").fadeOut(200, function() {
                        $("#loadingSpinner").css("display", "none");
                    });
                    console.error("Error occurred:", error);
                }
            });

        }
    });
});
//Publish Publication -- End

//Position Summary
$(document).off('click', '[id^="positionsummary_"]').on('click', '[id^="positionsummary_"]', function(e) {

    e.stopPropagation();

    var fetchdata = $(this);

    $.ajax({
        url: "backend/bk_positionsummary.php",
        method: "POST",
        data: {
            request: "viewpositionsummary",
            datavalue: fetchdata.data("datavalue"),
            userid: fetchdata.data("userid")
        },
        beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },
        success: function(dataResult) {
            $("#loadingSpinner").fadeOut(200, function() {
                $("#loadingSpinner").css("display", "none");
            });

            $('#attachmentmodallabel').html(fetchdata.data('openmodallabel'));
            $('#attachmentmodalcontent').html(dataResult);
            $('#attachmentmodal').modal('show');
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200, function() {
                $("#loadingSpinner").css("display", "none");
            });
            console.error("Error occurred:", error);
        }
    });
});
//Position Summary -- End

//View Status History
$(document).off('click', '[id^="view_status_"]').on('click', '[id^="view_status_"]', function(e) {
    var fetchdata = $(this);

    $.ajax({
        url: fetchdata.data("backendurl"),
        method: "POST",
        data: {
            request: fetchdata.data("backendrequest"),
            datavalue: fetchdata.data("datavalue")
        },
        beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },
        success: function(dataResult) {
            $("#loadingSpinner").fadeOut(200, function() {
                $("#loadingSpinner").css("display", "none");
            });

            $('#addeditlabel').html(fetchdata.data('openmodallabel'));
            $(fetchdata.data("openmodalbody")).html(dataResult);
            $(fetchdata.data("openmodal")).modal('show');
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200, function() {
                $("#loadingSpinner").css("display", "none");
            });
            console.error("Error occurred:", error);
        }
    });
});
//View Status History -- End

//View Remarks History
$(document).off('click', '[id^="view_remarks_"]').on('click', '[id^="view_remarks_"]', function(e) {
    var fetchdata = $(this);

    $.ajax({
        url: fetchdata.data("backendurl"),
        method: "POST",
        data: {
            request: fetchdata.data("backendrequest"),
            datavalue: fetchdata.data("datavalue")
        },
        beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },
        success: function(dataResult) {
            $("#loadingSpinner").fadeOut(200, function() {
                $("#loadingSpinner").css("display", "none");
            });

            $('#addeditlabel').html(fetchdata.data('openmodallabel'));
            $(fetchdata.data("openmodalbody")).html(dataResult);
            $(fetchdata.data("openmodal")).modal('show');
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200, function() {
                $("#loadingSpinner").css("display", "none");
            });
            console.error("Error occurred:", error);
        }
    });
});
//View Remarks History -- End

//View Profile
$(document).off('click', '[id^="viewprofile_"]').on('click', '[id^="viewprofile_"]', function(e) {
    var fetchdata = $(this);

    $.ajax({
        url: "backend/bk_viewattachment.php",
        method: "POST",
        data: {
            request: "viewprofile",
            datavalue: fetchdata.data("datavalue")
        },
        beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },
        success: function(dataResult) {
            $("#loadingSpinner").fadeOut(200, function() {
                $("#loadingSpinner").css("display", "none");
            });

            $('#attachmentmodallabel').html(fetchdata.data('openmodallabel'));
            $('#attachmentmodalcontent').html(dataResult);
            $('#attachmentmodal').modal('show');
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200, function() {
                $("#loadingSpinner").css("display", "none");
            });
            console.error("Error occurred:", error);
        }
    });
});
//View Profile -- End

//Profile Picture Management
$(document).off('click', '#profilepictrigger').on('click', '#profilepictrigger', function(e) {
    e.stopPropagation();
    var fetchdata = $(this);

    $.ajax({
        url: "backend/bk_changeprofilepic.php",
        method: "POST",
        data: {
            request: "changeprofilepic",
            datavalue: fetchdata.data("datavalue")
        },
        beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },
        success: function(dataResult) {
            $("#loadingSpinner").fadeOut(200, function() {
                $("#loadingSpinner").css("display", "none");
            });

            $('#addeditlabel').html(fetchdata.data('openmodallabel'));
            $('#addeditcontent').html(dataResult);
            $('#addeditmodal').modal('show');
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200, function() {
                $("#loadingSpinner").css("display", "none");
            });
            console.error("Error occurred:", error);
        }
    });
});

$(document).on("change", "#uploadPic", function (e) {
    const file = this.files[0];

    if (!file) return;

    if (!file.type.startsWith("image/")) {
        Swal.fire({
            icon: "error",
            title: "Invalid file",
            text: "Please select an image file."
        });
        $(this).val("");
        return;
    }

    const reader = new FileReader();

    reader.onload = function (e) {
        $("#profilePreview")
            .attr("src", e.target.result)
            .hide()
            .fadeIn(200);
    };

    reader.readAsDataURL(file);
});

$(document).off('click', '#saveProfilePic').on('click', '#saveProfilePic', function(e) {

    e.stopPropagation();

    var fetchdata = $(this);
    var input = $("#uploadPic")[0];

    if (!input.files.length) {
        Swal.fire({
            title: "No image selected",
            text: "Please choose a profile picture.",
            icon: "warning",
            confirmButtonText: "OK",
            scrollbarPadding: false
        });
        return;
    }

    let formData = new FormData();
    formData.append("request", fetchdata.data("backendrequest"));
    formData.append("datavalue", $("#profilepictrigger").data("datavalue"));
    formData.append("profile_pic", input.files[0]);

    $.ajax({
        url: fetchdata.data("backendurl"),
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,

        beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
            fetchdata.prop("disabled", true);
        },

        success: function(dataResult) {
            $("#loadingSpinner").fadeOut(200, function() {
                $("#loadingSpinner").css("display", "none");
            });

            fetchdata.prop("disabled", false);

            let res;
            try {
                res = typeof dataResult === "object" ? dataResult : JSON.parse(dataResult);
            } catch (e) {
                console.error("Invalid JSON:", dataResult);
                Swal.fire("Error", "Invalid server response.", "error");
                return;
            }

            if (res.status === 'success') {
                $('#addeditmodal').modal('hide');

                let newImg = '/JobPortal/' + res.new_image.replace('../', '');

                // update sidebar image
                $('.avatar-circle img').attr('src', newImg);

                Swal.fire({
                    title: "Upload successful",
                    text: res.message || "Upload successful.",
                    icon: "success",
                    confirmButtonText: "OK",
                    scrollbarPadding: false
                });

            } else {
                Swal.fire({
                    title: "Upload failed",
                    text: res.message || "Something went wrong.",
                    icon: "error",
                    confirmButtonText: "OK",
                    scrollbarPadding: false
                });
            }
        },

        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200, function() {
                $("#loadingSpinner").css("display", "none");
            });

            fetchdata.prop("disabled", false);
            console.error("Error occurred:", error);

            Swal.fire({
                title: "Upload failed",
                text: "Something went wrong.",
                icon: "error",
                confirmButtonText: "OK",
                scrollbarPadding: false
            });
        }
    });

});
//Profile Picture Management -- End
