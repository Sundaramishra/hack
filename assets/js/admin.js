document.addEventListener('DOMContentLoaded', function() {
    // File input preview
    const fileInputs = document.querySelectorAll('.file-input');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const previewId = this.dataset.preview;
            if (!previewId) return;
            
            const previewElement = document.getElementById(previewId);
            if (!previewElement) return;
            
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const fileType = file.type;
                const validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
                const validVideoTypes = ['video/mp4', 'video/webm'];
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    if (validImageTypes.includes(fileType)) {
                        previewElement.innerHTML = `<img src="${e.target.result}" class="max-h-40 rounded">`;
                    } else if (validVideoTypes.includes(fileType)) {
                        previewElement.innerHTML = `
                            <video controls class="max-h-40 rounded">
                                <source src="${e.target.result}" type="${fileType}">
                                Your browser does not support the video tag.
                            </video>
                        `;
                    } else {
                        previewElement.innerHTML = `<p class="text-gray-500">File selected: ${file.name}</p>`;
                    }
                }
                
                reader.readAsDataURL(file);
            } else {
                previewElement.innerHTML = '';
            }
        });
    });
    
    // Confirm delete action
    const deleteButtons = document.querySelectorAll('.delete-button');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
    
    // Multi-image upload preview
    const multiFileInput = document.getElementById('portfolio-images');
    
    if (multiFileInput) {
        multiFileInput.addEventListener('change', function() {
            const previewContainer = document.getElementById('image-preview-container');
            if (!previewContainer) return;
            
            previewContainer.innerHTML = '';
            
            if (this.files && this.files.length > 0) {
                for (let i = 0; i < this.files.length; i++) {
                    const file = this.files[i];
                    const fileType = file.type;
                    const validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
                    
                    if (validImageTypes.includes(fileType)) {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            const imagePreview = document.createElement('div');
                            imagePreview.className = 'relative inline-block mr-2 mb-2';
                            
                            imagePreview.innerHTML = `
                                <img src="${e.target.result}" class="h-24 w-24 object-cover rounded border">
                                <input type="hidden" name="image_order[]" value="${i}">
                            `;
                            
                            previewContainer.appendChild(imagePreview);
                        }
                        
                        reader.readAsDataURL(file);
                    }
                }
            }
        });
    }
    
    // Add form fields dynamically for portfolio images
    const addImageButton = document.getElementById('add-image-button');
    
    if (addImageButton) {
        addImageButton.addEventListener('click', function() {
            const imagesContainer = document.getElementById('additional-images');
            const imageCount = imagesContainer.querySelectorAll('.image-upload-field').length;
            
            const newField = document.createElement('div');
            newField.className = 'image-upload-field mb-4 p-4 border rounded';
            
            newField.innerHTML = `
                <div class="mb-2 flex justify-between items-center">
                    <label class="form-label">Image ${imageCount + 1}</label>
                    <button type="button" class="remove-image-button text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i> Remove
                    </button>
                </div>
                <input type="file" name="additional_images[]" class="form-input file-input" data-preview="preview-${imageCount}" accept="image/*">
                <div id="preview-${imageCount}" class="mt-2"></div>
                <input type="hidden" name="image_position[]" value="${imageCount}">
            `;
            
            imagesContainer.appendChild(newField);
            
            // Add event listener to the new remove button
            const removeButton = newField.querySelector('.remove-image-button');
            removeButton.addEventListener('click', function() {
                newField.remove();
            });
        });
    }
    
    // Remove image field
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-image-button') || 
            e.target.parentElement && e.target.parentElement.classList.contains('remove-image-button')) {
            
            const button = e.target.classList.contains('remove-image-button') ? 
                e.target : e.target.parentElement;
            
            const field = button.closest('.image-upload-field');
            if (field) {
                field.remove();
            }
        }
    });
    
    // Handle success messages auto-hide
    const successMessages = document.querySelectorAll('.success-message');
    
    successMessages.forEach(message => {
        setTimeout(() => {
            message.classList.add('opacity-0');
            setTimeout(() => {
                message.remove();
            }, 500);
        }, 3000);
    });
});
