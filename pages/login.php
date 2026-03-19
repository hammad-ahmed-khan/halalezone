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
    <title>Login - Halal Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" />
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
    .inputbox {
    position: relative;
    margin: 15px 0;
    width: 235px;
    border-bottom: 2px solid #fff;
}

.inputbox label {
    position: absolute;
    top: 50%;
    left: 5px;
    transform: translateY(-50%);
    color: #fff;
    font-size: 1rem;
    pointer-events: none;
    transition: all 0.5s ease-in-out;
}

 

.inputbox input:focus + label,
.inputbox input:not(:placeholder-shown) + label {
  top: -5px;
 
}

.inputbox input {
    width: 100%;
    height: 60px;
    background: transparent;
    border: none;
    outline: none;
    font-size: 1rem;
    padding: 0 35px 0 5px;
    color: #fff;
}

.inputbox ion-icon {
    position: absolute;
    right: 8px;
    color: #fff;
    font-size: 1.
}


.forget {
    margin: 25px 0;
    font-size: 0.85rem;
    color: #fff;
    display: flex;
    justify-content: space-between;
}

.forget label {
    display: flex;
    align-items: center;
}

.forget label input {
    margin-right: 3px;
}

.forget a {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
}

.forget a:hover {
    text-decoration: underline;
}

button {
    width: 100%;
    height: 40px;
    border-radius: 40px;
    background-color: rgb(255, 255, 255, 1);
    border: none;
    outline: none;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 600;
    transition: all 0.4s ease;
}

button:hover {
    background-color: rgb(255, 255, 255, 0.5);
}

.register {
    font-size: 0.9rem;
    color: #fff;
    text-align: center;
    margin: 25px 0 10px;
}

.register p a {
    text-decoration: none;
    color: #fff;
    font-weight: 600;
}

.register p a:hover {
    text-decoration: underline;
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
<main class="main login-container" id="top"class="bg-holder" style="background-image:url(img/schiff.jpg);">
      <div class="row vh-100 g-0">
        
         <div class="col-lg-6">
          <div class="row flex-center h-100 g-0 px-4 px-sm-0">
            <div class="col col-sm-6 col-lg-7 col-xl-6">
            <a class="d-flex flex-center text-decoration-none mb-4" href="#">
                <div class="d-flex align-items-center fw-bolder fs-3 d-inline-block"><img src="img/logo.png" style="width:250px;"/></div>
              </a>
            <form id="frmLogin" method="post" style="co2lor:#fff;">
              <div class="text-center mb-7">
                <h1>Sign In</h1>
               </div>              
               <div class="inputbox">
                <ion-icon name="mail-outline"></ion-icon>
                <input type="text" name="email" placeholder=" " id="email" required>
                <label for="">Username</label>
            </div>
            <div class="inputbox">
                <ion-icon name="lock-closed-outline"></ion-icon>
                <input type="password" name="password" placeholder=" " id="password" required>
                <label for="">Password</label>
            </div>
              <div class="row flex-between-center mb-7">
                <div class="col-auto">
                <div class="forget">
                  <div class="form-check mb-0 d-flex align-items-center">
                  <input class="form-check-input" type="checkbox" name="terms" id="terms" /> <span class="px-2"><label style="font-size: 0.8rem;" for="terms">I agree to the &nbsp; <a id="showterms" href="#" style="color:#355d39; text-decoration:underline;">Terms and Conditions</a></label></span>  
                  </div>
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

<div class="col-lg-6 position-relative d-none d-lg-block">
          <div ></div>
         </div>
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
                    <span class="title-version">(Version of 01.06.2025)</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                Coming Soon...
                <?php
                /*
                if (file_exists(__DIR__ . "/../terms.txt")) {
                    $terms = file_get_contents(__DIR__ . "/../terms.txt");
                    echo $terms;
                } else {
                    echo "No Terms and Conditions file found!";
                }
                    */
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