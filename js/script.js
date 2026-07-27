$(document).ready(function() {
    // Theme Toggle
    const savedTheme = localStorage.getItem('theme') || 'light';
    if (savedTheme === 'dark') {
        $('html').attr('data-theme', 'dark');
    }
    
    $(document).on('click', '#themeToggle', function() {
        if ($('html').attr('data-theme') === 'dark') {
            $('html').removeAttr('data-theme');
            localStorage.setItem('theme', 'light');
        } else {
            $('html').attr('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
        }
    });
    
    // Toggle Password Visibility
    $(document).on('click', '.toggle-password', function() {
        const input = $(this).siblings('input');
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Login Form Validation
    $('#loginForm').on('submit', function(e) {
        const username = $('#username').val().trim();
        const password = $('#password').val().trim();
        
        if (!username || !password) {
            e.preventDefault();
            alert('Please fill in all fields');
            return false;
        }
    });
    
    // Marks Preview
    $(document).on('input', '#internalMarks, #externalMarks', function() {
        const internal = parseInt($('#internalMarks').val()) || 0;
        const external = parseInt($('#externalMarks').val()) || 0;
        const total = internal + external;
        
        $('#totalPreview').text(total);
        
        const preview = $('#marksPreview');
        preview.removeClass('preview-pass preview-fail');
        
        if (total >= 40) {
            preview.addClass('preview-pass');
        } else {
            preview.addClass('preview-fail');
        }
    });
    
    // Filter Subjects by Semester
    $('#semesterSelect').on('change', function() {
        const semId = $(this).val();
        
        $('#subjectSelect option').each(function() {
            if ($(this).val() === '') return;
            
            if ($(this).data('semester') == semId) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        $('#subjectSelect').val('');
    });
    
    // Search/Filter Table
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#allResultsTable tbody tr').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(value) > -1);
        });
    });
    
    // Animate CGPA bars on scroll
    function animateBars() {
        $('.bar-fill').each(function() {
            const width = $(this).css('width');
            $(this).css('width', '0');
            $(this).animate({ width: width }, 1000);
        });
    }
    
    // Trigger animation when page loads
    if ($('.cgpa-chart').length) {
        animateBars();
    }
    
    // Confirm before leaving form with changes
    let formChanged = false;
    
    $('#resultForm').on('change input', function() {
        formChanged = true;
    });
    
    $(window).on('beforeunload', function() {
        if (formChanged) {
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });
    
    $('#resultForm').on('submit', function() {
        formChanged = false;
    });
});
