document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('cv-collector-form').addEventListener('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        formData.append('action', 'cv_collector_save_data');

        fetch(cv_collector_params.ajax_url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            var responseElement = document.getElementById('cv-collector-response');
            if (data.success) {
                responseElement.innerHTML = '<p style="color:green;">' + data.data.message + '</p>';
                document.getElementById('cv-collector-form').reset();
            } else {
                responseElement.innerHTML = '<p style="color:red;">' + data.data.message + '</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('cv-collector-response').innerHTML = '<p style="color:red;">There was an error submitting your CV.</p>';
        });
    });

    document.getElementById('province').addEventListener('change', function () {
        var province = this.value;
        var districtSelect = document.getElementById('district');
    
        districtSelect.innerHTML = '';
    
        var defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Select District';
        districtSelect.appendChild(defaultOption);
    
        var districts = {
            central: ['Kandy', 'Matale', 'Nuwara Eliya'],
            sabaragamuwa: ['Ratnapura', 'Kegalle'],
            eastern: ['Batticaloa', 'Ampara', 'Trincomalee'],
            north_central: ['Anuradhapura', 'Polonnaruwa'],
            northern: ['Jaffna', 'Kilinochchi', 'Mannar', 'Mullaitivu', 'Vavuniya'],
            uva: ['Badulla', 'Monaragala'],
            north_western: ['Kurunegala', 'Puttalam'],
            western: ['Colombo', 'Gampaha', 'Kalutara'],
            southern: ['Galle', 'Matara', 'Hambantota']
        };
    
        if (districts[province]) {
            districts[province].forEach(function(district) {
                var option = document.createElement('option');
                option.value = district;
                option.textContent = district;
                districtSelect.appendChild(option);
            });
        }
    });

    document.querySelectorAll('.delete-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            var row = this.closest('tr');
            var id = row.getAttribute('data-id');

            if (confirm('Are you sure you want to delete this record?')) {
                jQuery.ajax({
                    url: cv_collector_params.ajax_url,
                    method: 'POST',
                    data: {
                        action: 'cv_collector_delete_data',
                        id: id
                    },
                    success: function(response) {
                        if (response.success) {
                            row.remove();
                            alert(response.data.message);
                        } else {
                            console.error('Error:', response.data.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                    }
                });
            }
        });
    });
});
