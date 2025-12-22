<?php
if ($user = $amdb->get_row("SELECT * FROM hqc_admin_users WHERE uid='$_GET[uid]'")) {
    $evaluation = trim($user['evaluation']) != '' ? unserialize($user['evaluation']) : [];    // Get the evaluation data
    $evaluation['pros'] = $evaluation['pros'] ?? array();    // Set the pros array if it doesn't exist
    $evaluation['cons'] = $evaluation['cons'] ?? array();    // Set the cons array if it doesn't exist
    $evaluation['performanceRating'] = $evaluation['performanceRating'] ?? 1;    // Set the ratings array if it doesn't exist
    $evaluation['teamworkRating'] = $evaluation['teamworkRating'] ?? 1;    // Set the ratings array if it doesn't exist
    $evaluation['punctualityRating'] = $evaluation['punctualityRating'] ?? 1;    // Set the ratings array if it doesn't exist
    $evaluation['finalThoughts'] = $evaluation['finalThoughts'] ?? '';    // Set the final thoughts if it doesn't exist
    $evaluation['finalRating'] = $evaluation['finalRating'] ?? 1;    // Set the final rating if it doesn't exist
?>
    <style>
        .form-group {
            margin-bottom: 15px;
        }

        .pros,
        .cons {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
        }

        .pros li,
        .cons li {
            list-style: none;
            margin-bottom: 5px;
        }

        #ratingList span {
            display: inline-block;
            width: 200px;
        }

        ul {
            padding: 0px;
        }

        ul li {

            padding-left: 20px;
            margin-bottom: 5px;
        }

        i.fa-plus.fas {
            color: green;
        }

        i.fas.fa-minus {
            color: red;
        }

        #prosList li input,
        #consList li input {
            width: 90%;
        }

        #prosList li,
        #consList li {
            padding: 5px 20px;
        }
    </style>
    <h2 class="content_title" style="text-align:center;">Auditor Evaluation Form</h2>

    <form id="evaluationForm" target="" action="user_evaluate_save.php" method="post" onsubmit="post_this_form(this)">
        <input type="hidden" name="uid" value="<?php echo $_GET['uid']; ?>" />
        <input type="hidden" name="act" value="update_evaluation" />
        <h4 class="form-group">
            <b>Member Name:</b> <?php echo $user['username_owner']; ?>
        </h4>

        <fieldset class="pros">
            <legend>Pros (Advantages of having <b><?php echo $user['username_owner']; ?></b> on our team):</legend>
            <ul id="prosList" class="alternate">
                <?php if (count($evaluation['pros']) > 0) {
                    foreach ($evaluation['pros'] as $pros) { ?>
                        <li><i class="fas fa-plus"></i> <input type="text" name="evaluation[pros][]" value="<?php echo $pros; ?>" required>
                            <i class="far fa-trash-alt" onclick="removeProsCons(this)"><span>Remove</span></i>
                        </li>
                <?php }
                } ?>
            </ul>
            <button type="button" onclick="addPros()">Add Pro</button>
        </fieldset>

        <fieldset class="cons">
            <legend>Cons (Disadvantages of having <b><?php echo $user['username_owner']; ?></b> on our team):</legend>
            <ul id="consList" class="alternate">
                <?php if (count($evaluation['cons']) > 0) {
                    foreach ($evaluation['cons'] as $cons) { ?>
                        <li><i class="fas fa-minus"></i> <input type="text" name="evaluation[cons][]" value="<?php echo $cons; ?>" required>
                            <i class="far fa-trash-alt" onclick="removeProsCons(this)"><span>Remove</span></i>
                        </li>
                <?php }
                } ?>
            </ul>
            <button type="button" onclick="addCons()">Add Con</button>
        </fieldset>
        <div class="form-group">
            <h3>Rating:</h3>
            <ul id="ratingList" class="alternate">
                <li>
                    <span>Performance Rating (1-10):</span>
                    <input type="number" id="performanceRating" name="evaluation[performanceRating]" min="1" max="10" value="<?php echo $evaluation['performanceRating']; ?>">
                </li>

                <li>
                    <span>Teamwork Rating (1-10):</span>
                    <input type="number" id="teamworkRating" name="evaluation[teamworkRating]" min="1" max="10" value="<?php echo $evaluation['teamworkRating']; ?>">
                </li>
                <li>
                    <span>Punctuality Rating (1-10):</span>
                    <input type="number" id="punctualityRating" name="evaluation[punctualityRating]" min="1" max="10" value="<?php echo $evaluation['punctualityRating']; ?>">
                </li>
            </ul>
        </div>
        <div class="form-group">
            <h3>Overall Rating:</h3>
            <ul class="alternate" id="overallRatingList">
                <li><span class="stars_5 rating-starts"></span><label><input type="radio" name="evaluation[finalRating]" value="5" <?php echo $evaluation['finalRating'] == 5 ? 'checked' : ''; ?> />5 - Excellent</label></li>
                <li><span class="stars_4 rating-starts"></span><label><input type="radio" name="evaluation[finalRating]" value="4" <?php echo $evaluation['finalRating'] == 4 ? 'checked' : ''; ?> />4 - Good</label></li>
                <li><span class="stars_3 rating-starts"></span><label><input type="radio" name="evaluation[finalRating]" value="3" <?php echo $evaluation['finalRating'] == 3 ? 'checked' : ''; ?> />3 - Satisfactory</label></li>
                <li><span class="stars_2 rating-starts"></span><label><input type="radio" name="evaluation[finalRating]" value="2" <?php echo $evaluation['finalRating'] == 2 ? 'checked' : ''; ?> />2 - Needs Improvement</label></li>
                <li><span class="stars_1 rating-starts"></span><label><input type="radio" name="evaluation[finalRating]" value="1" <?php echo $evaluation['finalRating'] == 1 ? 'checked' : ''; ?> />1 - Poor</label></li>
            </ul>
        </div>
        <div class="form-group">
            <b>Remarks / Recommendations:</b><br />
            <textarea id="finalThoughts" name="evaluation[finalThoughts]" style="width: 100%;" rows="5"><?php echo $evaluation['finalThoughts']; ?></textarea>
        </div>

        <div style="text-align:center;">
            <button type="submit">Save Evaluation</button> <button type="button" onclick="location='?inc=admin_users&type=auditor'">Cancel Evaluation</button> <?php if (trim($user['evaluation']) != '') { ?><button type="button" onclick="removeEvaluation()">Remove Evaluation</button><?php }; ?>
        </div>
    </form>

    <script>
        // Function to add pros
        function addPros() {
            const prosList = document.getElementById("prosList");
            const newProsInput = document.createElement("li");
            newProsInput.innerHTML = '<i class="fas fa-plus"></i> <input type="text" name="evaluation[pros][]" required>' +
                '<i class="far fa-trash-alt" onclick="removeProsCons(this)"><span>Remove</span></i>';
            prosList.appendChild(newProsInput);
        }

        // Function to add cons
        function addCons() {
            const consList = document.getElementById("consList");
            const newConsInput = document.createElement("li");
            newConsInput.innerHTML = '<i class="fas fa-minus"></i> <input type="text" name="evaluation[cons][]" required>' +
                '<i class="far fa-trash-alt" onclick="removeProsCons(this)"><span>Remove</span></i>';
            consList.appendChild(newConsInput);
        }

        // Function to remove pros/cons
        function removeProsCons(element) {
            element.parentNode.remove();
        }

        async function removeEvaluation() {
            await confirm_message("Are you sure you want to remove this evaluation?");
            $.post("user_evaluate_save.php", {
                act: "remove_evaluation",
                uid: '<?php echo $_GET['uid']; ?>'
            }, function(data) {
                if (data == 'success') {
                    location = '?inc=admin_users&type=auditor';
                } else {
                    alert_message(data);
                }
            });
        }
    </script>

<?php }
