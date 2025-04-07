/********f************
    
    Project 1 Javascript
    Name: Oksana Sokolova 
    Date: November 18 2024
    Description: JavaScript functionality for Project 4.

*********************/
/*
 * Handles the submit event of the survey form
 *
 * param e  A reference to the event object
 * return   True if no validation errors; False if the form has
 *          validation errors
 */
function validate(e) {
    // Hides all error elements on the page
    hideErrors();

    // Determine if the form has errors
    if (formHasErrors()) {
        // Prevents the form from submitting
        e.preventDefault();

        // When using onSubmit="validate()" in markup, returning false would prevent
        // the form from submitting
        return false;
    }

    // Hide errors explicitly to ensure clean submission
    hideErrors();

    // When using onSubmit="validate()" in markup, returning true would allow
    // the form to submit
    return true;
}

/*
 * Handles the reset event for the form.
 *
 * param e  A reference to the event object
 * return   True allows the reset to happen; False prevents
 *          the browser from resetting the form.
 */
function resetForm(e) {
    // Confirm that the user wants to reset the form.
    if (confirm('Clear the form?')) {
        // Ensure all error fields are hidden
        hideErrors();

        // Set focus to the first text field on the page
        document.getElementById("name").focus();

        // When using onReset="resetForm()" in markup, returning true will allow
        // the form to reset
        return true;
    }

    // Prevents the form from resetting
    e.preventDefault();

    // When using onReset="resetForm()" in markup, returning false would prevent
    // the form from resetting
    return false;
}

function formHasErrors() {
    let errorFlag = false;

    let requiredFields = ["name", "phone", "email"];
    for(let i = 0; i < requiredFields.length; i++){
        let textField = document.getElementById(requiredFields[i]);
        if(!formFieldHasInput(textField)){
            // Show error message for empty field
            document.getElementById(requiredFields[i] + "_error").style.display = "block";
            // Focus on the first field with an error
            if(!errorFlag){
                textField.focus();
                textField.select();
            }

            //Raise the error flag
            errorFlag = true;
        } else {
            // Hide the error message for valid fields
            document.getElementById(requiredFields[i] + "_error").style.display = "none";
        }
    }

    // Validate phone number format
    let phoneValue = document.getElementById("phone").value;
    let phoneRegex = /^\d{10}$/;

    //Show error if phone number is invalid
    if(!phoneRegex.test(phoneValue)){
        // Show error if postal code is invalid
        document.getElementById("phone_error").style.display = "block";

        if(!errorFlag){
            document.getElementById("phone").focus();
            document.getElementById("phone").select();
        }
        
        errorFlag = true;
    }

        // Validate email format
    let emailValue = document.getElementById("email").value;
    let emailValueRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    //Show format error if email is invalid
    if(!emailValueRegex.test(emailValue)){
        // Show format error if email is invalid
        document.getElementById("email_error").style.display = "block";

        if(!errorFlag){
            document.getElementById("email").focus();
            document.getElementById("email").select();
        } 
        errorFlag = true;
    } 

    return errorFlag;
} 

/*
 * Determines if a text field element has input
 *
 * param   fieldElement A text field input element object
 * return  True if the field contains input; False if nothing entered
 */
function formFieldHasInput(fieldElement) {
    // Check if the text field has a value
    if (fieldElement.value == null || fieldElement.value.trim() == "") {
        // Invalid entry
        return false;
    }

    // Valid entry
    return true;
}   

/*
 * Hides all of the error elements.
 */
function hideErrors() {
    // Get an array of error elements
    let error = document.getElementsByClassName("error");

    // Loop through each element in the error array
    for (let i = 0; i < error.length; i++) {
        // Hide the error element by setting it's display style to "none"
        error[i].style.display = "none";
    }
}

/*
 * Handles the load event of the document.
 */
function load() {
    // Add event listener for the form submit
    document.getElementById("feedback").addEventListener("submit", validate);
    //Default browser reset
    document.getElementById("feedback").reset();
    // Add event listener for the customer reset
    document.getElementById("feedback").addEventListener("reset", resetForm);
}

// Add document load event listener
document.addEventListener("DOMContentLoaded", load);