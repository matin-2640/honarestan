<link rel="stylesheet" href="styles/add_student.css" />
<link rel="stylesheet" href="js/sweetalert2.min.css">

<a href="delete_course.php?id=<?php echo $courses['Co_ID']; ?>" class="btn-delete-student  "
    data-name="<?php echo $courses['Co_name']; ?>">
    حذف
</a>

<script src="js/sweetalert2.min.js"></script>
<script src="js/jquery-1.10.2.min.js"></script>
<script>
    $(document).on("click", ".btn-delete-student", function (e) {

        e.preventDefault();
        e.stopImmediatePropagation();

        var url = $(this).attr("href");
        var name = $(this).data("name");

        Swal.fire({
            title: "حذف درس",
            text: "آیا از حذف «" + name + "» مطمئن هستید؟",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "بله",
            cancelButtonText: "انصراف"
        }).then(function (result) {

            if (result.isConfirmed) {
                window.location.href = url;
            }

        });

        return false;
    });
</script>