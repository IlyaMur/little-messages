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
        },
        password: {
            required: true,
            minlength: 6,
            validPassword: true,
        },
        password_confirmation: {
            equalTo: '#inputPassword',
        },
    },
});
