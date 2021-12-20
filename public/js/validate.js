$.validator.setDefaults({
    highlight: (el) => {
        $(el).closest('.form-control').addClass('is-invalid');
    },
    unhighlight: (el) => {
        $(el).closest('.form-control').removeClass('is-invalid');
    },
    errorElement: 'span',
    errorClass: 'label label-danger',
    errorPlacement: function (error, el) {
        if (el.parent('.input-group').length) {
            error.insertAfter(el.parent());
        } else {
            error.insertAfter(el);
        }
    }
});

$.validator.addMethod(
    'validPassword',
    function (value, element, param) {
        if (value != '') {
            if (value.match(/.*[a-z]+.*/i) == null) {
                return false;
            }
            if (value.match(/.*\d+.*/) == null) {
                return false;
            }
        }

        return true;
    },
    'Must contain at least one letter and one number'
);

$('#formSignup').validate({
    rules: {
        name: 'required',
        email: {
            required: true,
            email: true,
            remote: '/account/validate-email',
        },
        password: {
            required: true,
            minlength: 6,
            validPassword: true,
        },
    },
    messages: {
        email: {
            remote: 'email already taken',
        },
    },
});

$('#formProfile').validate({
    rules: {
        name: 'required',
        email: {
            required: true,
            email: true,
            remote: {
                url: '/account/validate-email',
                data: {
                    ignoreId: () => userId
                }
            }
        },
        password: {
            minlength: 6,
            validPassword: true,
        },
    },
    messages: {
        email: {
            remote: 'email already taken',
        },
    },
});

$('#formPost').validate({
    rules: {
        title: 'required',
        body: 'required'
    },
    messages: {
        title: 'title is required',
        body: 'post body is required'
    }
});

$('#formPassword').validate({
    rules: {
        password: {
            required: true,
            minlength: 6,
            validPassword: true,
        },
    },
    messages: {
        email: {
            remote: 'email already taken',
        },
    },
});

