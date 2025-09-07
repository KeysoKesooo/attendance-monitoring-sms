<?php
  $page_title = 'Media';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(1);
?>
<?php $media_files = find_all('media');?>
<?php
  if(isset($_POST['submit'])) {
  $photo = new Media();
  $photo->upload($_FILES['file_upload']);
    if($photo->process_media()){
        $session->msg('s','photo has been uploaded.');
        redirect('media.php');
    } else{
      $session->msg('d',join($photo->errors));
      redirect('media.php');
    }

  }

?>
<?php include_once('layouts/header.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="libs/css/roles.scss" />
</head>

<body>

    <div class="row">
        <div class="col-md-12">
            <?php echo display_msg(isset($msg) ? $msg : ''); ?>
        </div>
    </div>

    <form class="action-buttons-container" action="media.php" method="POST" enctype="multipart/form-data">

        <div class="image-file-container">
            <input type="file" name="file_upload" multiple="multiple" class="search-bar" placeholder="File Upload...">
        </div>

        <a class="add_button" href="add_attendance.php">
            <span class="add_button__text">Upload File</span>
            <span class="add_button__icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" viewBox="0 0 24 24" stroke-width="2"
                    stroke-linejoin="round" stroke-linecap="round" stroke="currentColor" height="24" fill="none"
                    class="svg">
                    <line y2="19" y1="5" x2="12" x1="12"></line>
                    <line y2="12" y1="12" x2="19" x1="5"></line>
                </svg>
            </span>
        </a>
    </form>

    <div class="table">
        <div class="table-header">
            <div class="header__item"><a id="id" class="filter__link filter__link--number" href="#">ID</a></div>
            <div class="header__item"><a id="name" class="filter__link" href="#">Photo</a></div>
            <div class="header__item"><a id="username" class="filter__link" href="#">Photo Name</a></div>
            <div class="header__item"><a id="p_number" class="filter__link" href="#">Phone Type</a></div>
            <div class="header__item"><a class="filter__link" href="#">Actions</a></div>
        </div>
        <div class="table-content" id="table-content-media">
            <?php foreach ($media_files as $media_file): ?>

            <div class="table-row">
                <div class="table-data"><?php echo count_id();?></div>
                <div class="table-data">
                    <img class="img-avatar img-circle" src="uploads/student/<?php echo $media_file['file_name'];?>" />
                </div>
                <div class="table-data"><?php echo $media_file['file_name'];?></div>
                <div class="table-data"><?php echo $media_file['file_type'];?></div>
                <div class="table-data">
                    <a href="delete_media.php?id=<?php echo (int)$media_file['id']; ?>" class="btn btn-xs btn-danger"
                        data-toggle="tooltip" title="Remove" onclick="return confirmDelete();">
                        <i class="glyphicon glyphicon-remove"></i>
                    </a>
                </div>
            </div>
            <?php endforeach;?>
        </div>
    </div>
    <div class="pagination" id="pagination-controls-media"></div>


    <?php include_once('layouts/footer.php'); ?>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const entriesPerPage = 5; // Number of entries per page

        function paginateTable(contentId, paginationId) {
            const tableContent = document.getElementById(contentId);
            const rows = Array.from(tableContent.getElementsByClassName('table-row'));
            const paginationControls = document.getElementById(paginationId);
            const totalPages = Math.ceil(rows.length / entriesPerPage);

            let currentPage = 1;

            function renderPage(page) {
                // Hide all rows
                rows.forEach((row) => {
                    row.style.display = 'none';
                });

                // Show rows for the current page
                const start = (page - 1) * entriesPerPage;
                const end = start + entriesPerPage;
                rows.slice(start, end).forEach((row) => {
                    row.style.display = 'flex'; // Adjust to match your table layout
                });

                // Highlight the active page in pagination controls
                Array.from(paginationControls.children).forEach((btn, index) => {
                    btn.classList.toggle('active', index === page);
                });
            }

            function renderPagination() {
                paginationControls.innerHTML = '';

                // Add Previous button
                const prevBtn = document.createElement('button');
                prevBtn.textContent = 'Previous';
                prevBtn.disabled = currentPage === 1;
                prevBtn.addEventListener('click', () => {
                    currentPage -= 1;
                    renderPage(currentPage);
                });
                paginationControls.appendChild(prevBtn);

                // Add page numbers
                for (let i = 1; i <= totalPages; i++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.textContent = i;
                    pageBtn.className = currentPage === i ? 'active' : '';
                    pageBtn.addEventListener('click', () => {
                        currentPage = i;
                        renderPage(currentPage);
                    });
                    paginationControls.appendChild(pageBtn);
                }

                // Add Next button
                const nextBtn = document.createElement('button');
                nextBtn.textContent = 'Next';
                nextBtn.disabled = currentPage === totalPages;
                nextBtn.addEventListener('click', () => {
                    currentPage += 1;
                    renderPage(currentPage);
                });
                paginationControls.appendChild(nextBtn);
            }

            renderPagination();
            renderPage(currentPage);
        }

        // Initialize pagination for multiple tables
        paginateTable('table-content-media', 'pagination-controls-media');
    });
    </script>