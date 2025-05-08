<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get announcement ID from URL
if (!isset($_GET['id'])) {
    header("Location: admin_announcements.php");
    exit();
}

$id = (int)$_GET['id'];

// Database connection
$conn = new mysqli("localhost", "root", "", "studentsportal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch announcement data
$stmt = $conn->prepare("SELECT * FROM announcements WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$announcement = $result->fetch_assoc();

if (!$announcement) {
    $conn->close();
    header("Location: admin_announcements.php");
    exit();
}

// Close connection (we'll reopen if form is submitted)
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Announcement | SONIT</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #003366;
            --secondary-blue: #005b96;
            --accent-gold: #d4af37;
            --light-gray: #f8f9fa;
        }
        
        body {
            background-color: var(--light-gray);
            padding-top: 20px;
        }
        
        .edit-form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .form-header h3 {
            color: var(--primary-blue);
            font-weight: 700;
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-blue);
            border-color: var(--secondary-blue);
        }
        
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }
        
        .image-preview {
            position: relative;
            width: 120px;
            height: 120px;
        }
        
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .delete-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="edit-form-container">
        <div class="form-header">
            <h3><i class="fas fa-edit mr-2"></i>Edit Announcement</h3>
        </div>
        
        <form id="editAnnouncementForm" method="POST" action="update_announcement.php">
            <input type="hidden" name="id" value="<?php echo $announcement['id']; ?>">
            
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" class="form-control" id="title" name="title" 
                       value="<?php echo htmlspecialchars($announcement['title']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="content">Content</label>
                <textarea class="form-control" id="content" name="content" rows="8" 
                          required><?php echo htmlspecialchars($announcement['content']); ?></textarea>
            </div>
            
            <!-- Current Images -->
            <?php 
            $images = json_decode($announcement['images'], true) ?: [];
            if (!empty($images)): 
            ?>
                <div class="form-group">
                    <label>Current Images</label>
                    <div class="image-preview-container">
                        <?php foreach ($images as $index => $image): ?>
                            <div class="image-preview">
                                <img src="uploads/<?php echo htmlspecialchars($image); ?>" alt="Announcement Image">
                                <button type="button" class="delete-image" data-image="<?php echo htmlspecialchars($image); ?>">
                                    <i class="fas fa-times"></i>
                                </button>
                                <input type="hidden" name="existing_images[]" value="<?php echo htmlspecialchars($image); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- New Images -->
            <div class="form-group">
                <label for="new_images">Add More Images (Optional)</label>
                <input type="file" class="form-control-file" id="new_images" name="new_images[]" multiple accept="image/*">
                <small class="form-text text-muted">Max 5MB per image (JPG, PNG, GIF)</small>
                
                <div id="newImagesPreview" class="image-preview-container mt-3"></div>
            </div>
            
            <div class="form-group text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save mr-2"></i>Update Announcement
                </button>
                <a href="admin_announcement.php" class="btn btn-secondary btn-lg ml-2">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        // Preview new images before upload
        $('#new_images').change(function() {
            $('#newImagesPreview').empty();
            const files = this.files;
            
            for (let i = 0; i < files.length; i++) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#newImagesPreview').append(`
                        <div class="image-preview">
                            <img src="${e.target.result}" alt="Preview">
                        </div>
                    `);
                }
                reader.readAsDataURL(files[i]);
            }
        });
        
        // Handle image deletion
        $(document).on('click', '.delete-image', function() {
            const imageName = $(this).data('image');
            $(this).parent().remove();
            $('#editAnnouncementForm').append(
                `<input type="hidden" name="deleted_images[]" value="${imageName}">`
            );
        });
    </script>
</body>
</html>