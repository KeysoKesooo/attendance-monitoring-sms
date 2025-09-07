
function confirmDelete() {
    return confirm("Are you sure you want to delete this user?");
}


$(document).ready(function() {

    //tooltip
    $('[data-toggle="tooltip"]').tooltip();
 
    $('.submenu-toggle').click(function () {
       $(this).parent().children('ul.submenu').toggle(200);
    });
    //suggetion for finding product names
    suggetion();
    // Callculate total ammont
    total();
 
    $('.datepicker')
        .datepicker({
            format: 'yyyy-mm-dd',
            todayHighlight: true,
            autoclose: true
        });
  });

  






document.addEventListener('DOMContentLoaded', function() {
    const sectionSelect = document.getElementById('section');
    const gradeLevelSelect = document.getElementById('grade_level');

    sectionSelect.addEventListener('change', function() {
        const selectedOption = sectionSelect.options[sectionSelect.selectedIndex];
        const gradeLevel = selectedOption.getAttribute('data-grade');

        // Clear previous options
        gradeLevelSelect.innerHTML = '<option value="">Grade Level</option>';

        if (gradeLevel) {
            // Set the grade level
            gradeLevelSelect.innerHTML += `<option value="${gradeLevel}">${gradeLevel}</option>`;
        }
    });
});


function suggetion() {

    $('#sug_input').keyup(function(e) {

        var formData = {
            'student_name' : $('input[name=title]').val()
        };

        if(formData['student_name'].length >= 1){

          // process the form
          $.ajax({
              type        : 'POST',
              url         : 'add_attendance_ajax.php',
              data        : formData,
              dataType    : 'json',
              encode      : true
          })
              .done(function(data) {
                  //console.log(data);
                  $('#result').html(data).fadeIn();
                  $('#result li').click(function() {

                    $('#sug_input').val($(this).text());
                    $('#result').fadeOut(500);

                  });

                  $("#sug_input").blur(function(){
                    $("#result").fadeOut(500);
                  });

              });

        } else {

          $("#result").hide();

        };

        e.preventDefault();
    });

}
 $('#sug-form').submit(function(e) {
     var formData = {
         'p_name' : $('input[name=title]').val()
     };
       // process the form
       $.ajax({
           type        : 'POST',
           url         : 'add_attendance_ajax.php',
           data        : formData,
           dataType    : 'json',
           encode      : true
       })
           .done(function(data) {
               //console.log(data);
               $('#student_info').html(data).show();
               total();
               $('.datePicker').datepicker('update', new Date());

           }).fail(function() {
               $('#student_info').html(data).show();
           });
     e.preventDefault();
 });

 $('#sug-form').submit(function(e) {
     var formData = {
         'p_name' : $('input[name=title]').val()
     };
       // process the form
       $.ajax({
           type        : 'POST',
           url         : 'add_attendance_ajax.php',
           data        : formData,
           dataType    : 'json',
           encode      : true
       })
           .done(function(data) {
               //console.log(data);
               $('#student_info').html(data).show();
               total();
               $('.datePicker').datepicker('update', new Date());

           }).fail(function() {
               $('#student_info').html(data).show();
           });
     e.preventDefault();
 });
 
 $(document).ready(function () {
    // Handle filter form submission
    $('#attendance-form').submit(function (e) {
        e.preventDefault();
        
        // Show loading spinner
        $('#custom-attendance-info').html('<p>Loading...</p>');

        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();

        // Send AJAX request to filter data
        $.ajax({
            url: 'custom_attendance_ajax.php',
            type: 'POST',
            data: {
                start_date: start_date,
                end_date: end_date
            },
            success: function (data) {
                var response = JSON.parse(data);

                // Update table content
                $('#custom-attendance_info').html(response.table);
                $('#pagination-controls-custom').html(response.pagination);
            }
        });
    });

    // Handle download link click
    $('#download-custom-button').click(function () {
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        var link = $(this).attr('href') + '&start_date=' + start_date + '&end_date=' + end_date;
        $(this).attr('href', link);
    });
});

const toggleButton = document.getElementById("toggle-btn");
const sidebar = document.getElementById("sidebar");

function toggleSidebar() {
  sidebar.classList.toggle("close");
  toggleButton.classList.toggle("rotate");

  closeAllSubMenus();
}

function toggleSubMenu(button) {
  if (!button.nextElementSibling.classList.contains("show")) {
    closeAllSubMenus();
  }

  button.nextElementSibling.classList.toggle("show");
  button.classList.toggle("rotate");

  if (sidebar.classList.contains("close")) {
    sidebar.classList.toggle("close");
    toggleButton.classList.toggle("rotate");
  }
}

function closeAllSubMenus() {
  Array.from(sidebar.getElementsByClassName("show")).forEach((ul) => {
    ul.classList.remove("show");
    ul.previousElementSibling.classList.remove("rotate");
  });
}

// Add event listeners to submenu items to stop event propagation
document.querySelectorAll('.sub-menu a').forEach(item => {
  item.addEventListener('click', event => {
    event.stopPropagation();
  });
});

// Open Popup
const openPopup = document.getElementById('openPopup');
const popupForm = document.getElementById('popupForm');
const closePopup = document.getElementById('closePopup');

openPopup.addEventListener('click', () => {
    popupForm.style.display = 'flex';
});

// Close Popup
closePopup.addEventListener('click', () => {
    popupForm.style.display = 'none';
});

// Close Popup on Outside Click
window.addEventListener('click', (event) => {
    if (event.target === popupForm) {
        popupForm.style.display = 'none';
    }
});