<?php
include_once('config/config.php');
include_once('classes/users.php');
$GLOBALS['appVersion'] = 0.0;
$is_login_page = true;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php // include_once('pages/header.php');?>
    <title>Login - Halal e-Zone</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/login.css?v=<?php echo rand(); ?>" /> 
    <style>
/* styles.css */
.cookie-overlay {
    display: none; /* Initially hidden */
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background-color: rgba(0, 0, 0, 0.8); /* Dark overlay with opacity */
    color: white;
    text-align: center;
    padding: 20px;
    z-index: 1000;
}


.cookie-message {
    max-width: 100%;
    margin: auto;
}

.cookie-message a {
    color: #f0f0f0;
    text-decoration: underline;
}

.cookie-message button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    margin: 0 5px;
}

.cookie-message button:hover {
    background-color: #0056b3;
}

.cookie-message button.reject {
    background-color: #dc3545; /* Red for reject */
}

.cookie-message button.reject:hover {
    background-color: #c82333;
}

.cookie-overlay.show {
    display: block;
}

.login-container {
    /* Ensure this content is not accessible when overlay is displayed */
 }

#webinarModal .modal-body {
    padding: 0px !important;
}
.content-wrapper {
  display: flex;
  flex-direction: row;
}

.text-column {
    flex: 1;
    padding: 0px;
    background-color: #f6e397 !important;
    border-top-left-radius: 6px;
    border-bottom-left-radius: 6px;
    font-size:16px;
}
.image-column {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden; /* Ensures the image does not overflow */
}

.image-column img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-top-right-radius: 6px;
  border-bottom-right-radius: 6px;
}

.modal.fade .modal-dialog {
      transform: translate(0, -100%);
      transition: transform 0.3s ease-out;
    }

    .modal.fade.show .modal-dialog {
      transform: translate(0, 0);
    }

    </style>
</head>
<body>
<div id="cookie-overlay" class="cookie-overlay">
        <div class="cookie-message">
            <p>We use cookies to enhance your experience and provide personalized content. Please make sure cookies are enabled in your browser. By clicking "Accept," you consent to our use of cookies.</p>

            <button id="accept-cookies">Accept</button>
            <button id="reject-cookies">Reject</button>
        </div>
</div>    
<main class="main login-container" id="top">
      <div class="row vh-100 g-0">
        <div class="col-lg-6 position-relative d-none d-lg-block">
          <div class="bg-holder" style="background-image:url(img/bg.jpg);"></div>
         </div>
         <div class="col-lg-6">
          <div class="row flex-center h-100 g-0 px-4 px-sm-0">
            <div class="col col-sm-6 col-lg-7 col-xl-6">
            <a class="d-flex flex-center text-decoration-none mb-4" href="#">
                <div class="d-flex align-items-center fw-bolder fs-3 d-inline-block"><img src="img/logo.jpg" style="width:200px;"/></div>
              </a>
            <form id="frmLogin" method="post">
              <div class="text-center mb-7">
                <h3 class="text-body-highlight mb-2">Sign In</h3>
               </div>              
              <div class="mb-3 text-start"><label class="form-label" for="email">Login</label>
                <div class="form-icon-container"><input class="form-control form-icon-input" name="email" id="email" type="text" placeholder=""><svg class="svg-inline--fa fa-user text-body fs-9 form-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg=""><path fill="currentColor" d="M224 256c70.7 0 128-57.31 128-128s-57.3-128-128-128C153.3 0 96 57.31 96 128S153.3 256 224 256zM274.7 304H173.3C77.61 304 0 381.6 0 477.3c0 19.14 15.52 34.67 34.66 34.67h378.7C432.5 512 448 496.5 448 477.3C448 381.6 370.4 304 274.7 304z"></path></svg><!-- <span class="fas fa-user text-body fs-9 form-icon"></span> Font Awesome fontawesome.com --></div>
              </div>
              <div class="mb-3 text-start"><label class="form-label" for="password">Password</label>
                <div class="form-icon-container"><input class="form-control form-icon-input" name="password" id="password" type="password" placeholder=""><svg class="svg-inline--fa fa-key text-body fs-9 form-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="key" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M282.3 343.7L248.1 376.1C244.5 381.5 238.4 384 232 384H192V424C192 437.3 181.3 448 168 448H128V488C128 501.3 117.3 512 104 512H24C10.75 512 0 501.3 0 488V408C0 401.6 2.529 395.5 7.029 391L168.3 229.7C162.9 212.8 160 194.7 160 176C160 78.8 238.8 0 336 0C433.2 0 512 78.8 512 176C512 273.2 433.2 352 336 352C317.3 352 299.2 349.1 282.3 343.7zM376 176C398.1 176 416 158.1 416 136C416 113.9 398.1 96 376 96C353.9 96 336 113.9 336 136C336 158.1 353.9 176 376 176z"></path></svg><!-- <span class="fas fa-key text-body fs-9 form-icon"></span> Font Awesome fontawesome.com --></div>
              </div>
              <div class="row flex-between-center mb-7">
                <div class="col-auto">
                  <div class="form-check mb-0 d-flex align-items-center">
                  <input class="form-check-input" type="checkbox" name="terms" id="terms" /> <span class="px-2"><label style="font-size: 0.8rem;" for="terms">I agree to the <a id="showterms" href="#">Terms and Conditions</a></label></span>  
                  </div>
                </div>
               </div><button id="enter_btn" type="button" class="btn btn-primary w-100 mt-4" style="font-size: 0.8rem;">Sign In</button>              
               </form>

                <!--
                <div class="box"><div class="content-wrap d-flex align-items-center justify-content-center flex-column">
                        <img src="img/logo.jpg" style="width:200px;" class=" mb-4" />
                        <h3 class="mb-4">Log in</h3>
                        <form id="enter_form" class="form-horizontal">
                            <input class="form-control mb-3" type="text" name="email" placeholder="Login">
                            <input class="form-control mb-3" type="password" name="password" placeholder="Password">
                            <input type="checkbox" name="terms" id="terms" ><span>&nbsp;I agree to the <a id="showterms" href="#">Terms and Conditions</a></span>
                        <div id="res_enter"></div></form>
                            <div class="btn btn-primary  mt-4" id="enter_btn">
                                <i class="glyphicon glyphicon-log-in"></i>&nbsp;&nbsp;Login
                            </div>
           
                            </div>
        </div>
-->
<div class="mt-4"  style="font-size: 0.8rem;">
<?php include_once('pages/footer.php');?>
<!--
<div id="cookie-consent" class="cookie-consent">
  <p>We use cookies to improve your experience on our website. By clicking "Accept," you consent to our use of cookies. <a href="/privacy-policy">Learn more</a>.</p>
  <button id="accept-cookies">Accept</button>
</div>
-->
<script>
  document.getElementById('accept-cookies').addEventListener('click', function() {
    localStorage.setItem('cookiesAccepted001', 'true');
    document.getElementById('cookie-consent').style.display = 'none';
  });

  if (localStorage.getItem('cookiesAccepted001')) {
    document.getElementById('cookie-consent').style.display = 'none';
  }
</script>


                </div>
      </div>
    </main>
   
<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="title-main">Terms and Conditions of Use</span>
                    <span class="title-version">(Version of 03.12.2019)</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <?php
                if (file_exists(__DIR__ . "/../terms.txt")) {
                    $terms = file_get_contents(__DIR__ . "/../terms.txt");
                    echo $terms;
                } else {
                    echo "No Terms and Conditions file found!";
                }
                ?>
            </div>
            <div id="s_btn" class="modal-footer">
                <button type="button" class="btn" id="close_modal" data-bs-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="webinarModal" tabindex="-1" role="dialog" aria-labelledby="webinarModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" style="min-width: 1080px;" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <div class="content-wrapper">
          <!-- Close button -->
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="position: absolute; top: 10px; right: 10px; z-index: 999;">
            <span aria-hidden="true"><i class="fas fa-times"></i></span>
          </button>

          <div class="text-column">
            <div style="padding:25px;"> 
              <h5 style="font-size:20px; font-weight:bold; margin-bottom:23px;">Halal et Halal QM : Formation pour Tous Niveaux !</h5>

              <p>Que vous soyez débutant ou déjà informé, maîtrisez les standards Halal grâce à notre formation !</p>

              <p>Participez à notre session complète le jeudi 25 avril 2024. Au programme :</p>
              <p>
              Introduction à l'Halal - Idéal pour les débutants !<br/>
              Approfondissement du Halal QM - Pour les participants déjà familiers</p>
              <p>Apprenez tout sur :</p>

              <p>Les principes fondamentaux de l'Halal<br/>
              Le système de management Halal QM (pas de jargon technique)<br/>
              Comment mettre en place l'Halal dans votre entreprise<br/>
              Cette formation convient à tous les niveaux.</p>

              <p>Places limitées </p>

              <p class="text-center"><a href="https://www.certification-halal.fr/halal-online-training/" class="btn btn-default" style="background-color: #feda52; font-size:18px; font-weight:bold;">inscrivez-vous dès aujourd'hui !</a></p>
            </div>
          </div>
          <div class="image-column">
            <img src="/img/webinar.jpg" alt="Webinar Image">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

 

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="js/sha512.js"></script>
</body>

<script type="text/javascript">
    $(document).ready(function(){

    <?php //if (isset($_GET['ants'])): ?>

     setTimeout(function() {
       // $('#webinarModal').modal('show');
    }, 1000); 

    <?php //endif; ?>

                $("#showterms").click(function() {
                    $('#myModal').modal('show');
                    return false;
                });

        $("#enter_form").keypress(function(e) {
            if (e.keyCode == 13)
            {
                $("#enter_btn").trigger("click");
            }
        });

        $('input[name="email"]').focus(function(){$('#res_enter').html('')});
        $('input[name="password"]').focus(function(){$('#res_enter').html('')});


        // Пользователь пытается войти
        $("#enter_btn").click(function() {
            if(!$('#terms:checked')[0])
            {
                alert("Please agree to the Terms and Conditions");
                return;
            }

            $('#res_enter').html('');
            // create hidden input for hashed password
            var p = $("<input>", {name: "p", type: "hidden", value: hex_sha512($('input[name="password"]').val())});
            $('form').append(p);
            // Make sure the plaintext password doesn't get sent.
            $('input[name="password"]').val("");
            var data = {};
            data.email = $('input[name="email"]').val();
            data.password = $('input[name="p"]').val();
            $('input[name="p"]').remove();

            // отправка данных для идентификации
            $.ajax({
                type: "POST",
                url: "ajax/ajaxHandler.php",
                data: {uid: 0, rtype: "login", data: data},
                cache: false,
                success: function(data) // результат
                {
                    var response = JSON.parse(data);
                    if(response.status == '1') {
                        
                        toastr.success('Welcome back!');
                      setTimeout(() => {
                        window.location.href = "";
                      }, 1000);                      
                    }
                    else {
                        toastr.error(response.statusDescription);
                        //$("#res_enter").html();
                    }
                }
            });
        });
    });

// scripts.js
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('cookie-overlay');
    const acceptButton = document.getElementById('accept-cookies');
    const rejectButton = document.getElementById('reject-cookies');

    // Show the overlay if consent is not given
    if (localStorage.getItem('cookiesAccepted001') === null || localStorage.getItem('cookiesAccepted001') === 'false') {
        overlay.classList.add('show');
        document.querySelector('.login-container').style.pointerEvents = 'none';
    }

    acceptButton.addEventListener('click', function() {
        localStorage.setItem('cookiesAccepted001', 'true');
        overlay.classList.remove('show');
        document.querySelector('.login-container').style.pointerEvents = 'auto';
    });

    rejectButton.addEventListener('click', function() {
        // Handle rejection (e.g., redirect, limit functionality)
        localStorage.setItem('cookiesAccepted001', 'false');
        overlay.classList.remove('show');
        document.querySelector('.login-container').style.pointerEvents = 'none';
        // Optional: Redirect or show a message
        //window.location.href = '/cookies-rejected'; // Redirect to a page explaining the limitations
    });
});


</script>

</html>