let registeredEmail = '';

// Load departments on page load
document.addEventListener('DOMContentLoaded', () => {
    loadDepartments();
});

// Handle role change
function handleRoleChange() {
    const role = document.getElementById('role').value;
    const classGroup = document.getElementById('classGroup');
    const rollNoGroup = document.getElementById('rollNoGroup');
    const classSelect = document.getElementById('class');
    const rollNoInput = document.getElementById('roll_no');
    
    if (role === 'Student') {
        classGroup.style.display = 'block';
        rollNoGroup.style.display = 'block';
        classSelect.required = true;
        rollNoInput.required = true;
    } else if (role === 'Staff') {
        classGroup.style.display = 'none';
        rollNoGroup.style.display = 'none';
        classSelect.required = false;
        rollNoInput.required = false;
        classSelect.value = '';
        rollNoInput.value = '';
    } else {
        classGroup.style.display = 'none';
        rollNoGroup.style.display = 'none';
        classSelect.required = false;
        rollNoInput.required = false;
    }
}

// Load departments
async function loadDepartments() {
    try {
        const response = await fetch('api/get-departments.php');
        const data = await response.json();
        
        if (data.success) {
            const departmentSelect = document.getElementById('department');
            departmentSelect.innerHTML = '<option value="">Select Department</option>';
            
            data.data.forEach(dept => {
                const option = document.createElement('option');
                option.value = dept.department_id;
                option.textContent = dept.department_name;
                departmentSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading departments:', error);
    }
}

// Load classes by department
async function loadClassesByDept() {
    const departmentId = document.getElementById('department').value;
    const classSelect = document.getElementById('class');
    
    if (!departmentId) {
        classSelect.innerHTML = '<option value="">Select Class</option>';
        return;
    }
    
    try {
        const response = await fetch(`api/get-classes-by-department.php?department_id=${departmentId}`);
        const data = await response.json();
        
        if (data.success) {
            classSelect.innerHTML = '<option value="">Select Class</option>';
            
            data.data.forEach(cls => {
                const option = document.createElement('option');
                option.value = cls.class_id;
                option.textContent = cls.class_name;
                classSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading classes:', error);
    }
}

document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const role = document.getElementById('role').value;
    const departmentId = document.getElementById('department').value;
    const classId = document.getElementById('class').value;
    const rollNo = document.getElementById('roll_no').value;
    
    // Build form data based on role
    const formData = {
        role: role,
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
        confirm_password: document.getElementById('confirm_password').value,
        department_id: departmentId
    };
    
    // Add student-specific fields
    if (role === 'Student') {
        if (!classId) {
            showMessage('Please select a class', 'error');
            return;
        }
        if (!rollNo) {
            showMessage('Please enter roll number', 'error');
            return;
        }
        formData.class_id = classId;
        formData.roll_no = rollNo;
    }
    
    try {
        const response = await fetch('api/register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            registeredEmail = formData.email;
            showMessage(data.message, 'success');
            document.getElementById('otpModal').style.display = 'block';
        } else {
            showMessage(data.message, 'error');
        }
    } catch (error) {
        showMessage('An error occurred. Please try again.', 'error');
    }
});

async function verifyOTP() {
    const otp = document.getElementById('otpInput').value;
    
    if (otp.length !== 6) {
        showOTPMessage('Please enter a valid 6-digit OTP', 'error');
        return;
    }
    
    try {
        const response = await fetch('api/verify-otp.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: registeredEmail,
                otp: otp,
                type: 'VERIFY'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showOTPMessage(data.message, 'success');
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 2000);
        } else {
            showOTPMessage(data.message, 'error');
        }
    } catch (error) {
        showOTPMessage('An error occurred. Please try again.', 'error');
    }
}

function showMessage(message, type) {
    const messageDiv = document.getElementById('message');
    messageDiv.textContent = message;
    messageDiv.className = 'message ' + type;
}

function showOTPMessage(message, type) {
    const messageDiv = document.getElementById('otpMessage');
    messageDiv.textContent = message;
    messageDiv.className = 'message ' + type;
}
