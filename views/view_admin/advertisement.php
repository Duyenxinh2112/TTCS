<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <title>Upload Image</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            color: #333;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
        <!--category-left -->
        <?php include("../TTCS/views/view_admin/category_left.php"); ?>
    <!--category-right -->
    <div class="category-right">
        <?php include("../TTCS/views/view_admin/header.php"); ?>
    <div class = "ad_form">
    <form id="uploadForm">
        <input type="file" name="image" id="imageInput" accept="image/*" required><br><br>

        <!-- Thêm trường nhập liệu cho mô tả -->
        <label for="name">Tên chương trình:</label><br>
        <textarea id="name" name="name" cols="50" required></textarea><br><br>
        <label for="description">Chi tiết:</label><br>
        <textarea id="description" name="description" rows="4" cols="50" required></textarea><br><br>

        <button class = "btn_ad" type="submit">Upload</button>
    </form>
    </div>

    <div class="infor">
                
                <table class="table table-striped" id="myDataTable">
                    <thead class="title">
                        <tr>
                            <th scope="col">Mã quảng cáo</th>
                            <th scope="col">Tên chương trình</th>
                            <th scope="col">Ảnh</th>
                            <th scope="col">Thêm thông tin</th>
                            <th scope="col">Hành động</th>
                    </thead>
                    <tbody class="tbody">

                    </tbody>
                </table>
            </div>
    <div id="message" class="error"></div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <script>
        document.getElementById('uploadForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Ngăn chặn hành vi gửi mặc định của biểu mẫu

            const formData = new FormData();
            const imageInput = document.getElementById('imageInput');
            const description = document.getElementById('description').value;
            const name = document.getElementById('name').value;

            // Lấy thời gian hiện tại làm create_at
            const createAt = new Date().toISOString();

            formData.append('image', imageInput.files[0]); // Thêm tệp hình ảnh vào FormData
            formData.append('description', description); // Thêm mô tả vào FormData
            formData.append('create_at', createAt); // Thêm thời gian tạo vào FormData
            formData.append('name', name);

            fetch('/app/api/upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Upload failed');
                }
                return response.text(); // Hoặc response.json() nếu API trả về JSON
            })
            .then(data => {
                document.getElementById('message').innerText = 'Upload successful: ' + data;
            })
            .catch(error => {
                document.getElementById('message').innerText = 'Error: ' + error.message;
            });
        });

        print_table();

function print_table() {
    fetch('http://localhost:3000/app/api/readAd.php')
        .then(response => response.json())
        .then(data => {
            // Đảm bảo rằng DataTable đã được hủy bỏ trước khi kích hoạt lại
            $('#myDataTable').DataTable().destroy();

            // Hiển thị dữ liệu trên DataTable
            $('#myDataTable').DataTable({
                data: data.data, // Giả sử API trả về một đối tượng với trường "data" chứa mảng dữ liệu
                columns: [
                    { data: 'maqc', title: 'Mã quảng cáo' },
                    { data: 'name', title: 'Tên chương trình' },
                    { data: 'img', title: 'Ảnh',
                        render: function(data) {
                            return `<img src="/uploads/quangCao/${data}" style="width:100px; height:auto;" alt="Image"/>`;
                        }
                    },
                    { data: 'description', title: 'Thông tin thêm' },
                    {
                        data: null,
                        title: 'Hành Động',
                        render: function (data) {
                            return `<button class='fix' onclick='edit(${data.maqc});'><i class='ti-pencil-alt'></i></button>
                                    <button class='trash' onclick='delete_ad(${data.maqc});'><i class='ti-trash'></i></button>`;
                        }
                    }
                ],
                destroy: true, // Đảm bảo hủy bỏ DataTable trước khi kích hoạt lại
            });
        })
        .catch(error => console.log(error));
}

function delete_ad(maqc) {
    Swal.fire({
        title: 'Bạn đã chắc chắn chưa?',
        text: "Bạn sẽ không còn dữ liệu sau khi xóa",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Tôi đồng ý'
    }).then((result) => {
        if (result.isConfirmed) {
            var url = "http://localhost:3000/app/api/deleteAd.php?maqc=";
            fetch(url + maqc, {
                method: 'delete',
                headers: {
                    "Content-Type": "application/json",
                    // 'Content-Type': 'application/x-www-form-urlencoded',
                }
            }).then(response => response.text())
                .then(data => {
                    var ticketItem = document.querySelector('.ticket-item-' + data.maVe);
                    if (ticketItem) {
                        ticketItem.remove();
                    }
                    console.log('Success', Response)
                    print_table(data);
                    Swal.fire(
                        'Đã xóa',
                        'Bạn đã xóa thành công.',
                        'success'
                    )
                })
                .catch(error => console.error('Error:', error));

        }
    })
}

function edit(maqc) {
    var url = "http://localhost:3000/app/api/readAd.php?maqc=" + maqc;
    fetch(url, {
        method: 'GET',
        headers: {
            "Content-Type": "application/json",
        }
    })
        .then(response => response.json())
        .then(data => {
            console.log('success', data);

            if (typeof data.data === 'object') {
                var item = data.data;

                // Hiển thị form chỉnh sửa với hình ảnh và các trường khác
                Swal.fire({
                    title: 'Chỉnh sửa thông tin quảng cáo',
                    html: `
            <form id="update_form" enctype="multipart/form-data">
                <label>Mã quảng cáo:</label>
                <input type="text" value="${item.maqc}" name="maqc" readonly><br><br>
                
                <label>Tên chương trình:</label>
                <input type="text" value="${item.name}" name="name" required><br><br>

                <label>Chi tiết:</label>
                <textarea name="description" required>${item.description}</textarea><br><br>

                <label>Địa điểm:</label>
                <textarea name="description" required>${item.place}</textarea><br><br>

                <label>Ảnh hiện tại:</label><br>
                <img src="/uploads/quangCao/${item.img}" style="width:100px;"><br><br>

                <label>Thay đổi ảnh (nếu có):</label>
                <input type="file" name="image"><br><br>
            </form>  
            `,
                    showCancelButton: true,
                    confirmButtonText: 'Lưu thay đổi'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData(document.querySelector("#update_form"));
                        const updateUrl = "http://localhost:3000/app/api/updateAd.php";

                        fetch(updateUrl, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Đã sửa', 'Cập nhật thành công', 'success');
                                print_table(); // Tải lại bảng sau khi cập nhật
                            } else {
                                Swal.fire('Lỗi', 'Có lỗi xảy ra khi cập nhật', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Lỗi', 'Có lỗi xảy ra khi cập nhật', 'error');
                        });
                    }
                });
            } else {
                console.error('Invalid data format.');
            }
        })
        .catch(function (error) {
            console.error('Error:', error);
        });
}

    </script>
</body>
</html>

