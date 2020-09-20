var $ = jQuery;

window.app = new Vue({
  el: '#registerSection',
  data: {
    phone_number: '',
    token: '',
    isDisable: true,
    alertType: 'alert-success',
    isRedirecting: false,
    redirectMessage: '',
    isTokendField: false,
    loading: false,
    errors: {}
  },
  methods: {
    onSubmit: function( event ) {
      var that = this;
      this.loading = true;
      $.ajax({
        type: "POST",
        url: event.target.action,
        data: this.$data,
        success: function( response ) {

          that.loading = false;

          if ( 'redirectUrl' in response ) {

            that.isRedirecting = true;
            that.redirectMessage = response.message;
            that.alertType = response.alertType;

            var handler = setTimeout(function() {
              window.location = response.redirectUrl;
            }, 1000);


            return;
          }

          // if ( 'buyer' in response ) {
          //   that.showPasswordField(event);
          // } else {
          //   that.recordErrors( response );
          // }

        },
        error: function( a, b, c ) {
          that.loading = false;
        }
      });
    },

    showTokenField: function(event) {

      this.isTokendField = true;

      $(event.target).find('input[type=password]').focus();

    },

    updateDisable: function() {
      this.isDisable = !this.phone_number.length > 0;
    },

    getError: function( field ) {
      if ( field ) {
        return this.errors.hasOwnProperty( field );
      }
      return false;
    },

    getErrorMessage: function( field ) {
      if ( field && this.errors.hasOwnProperty( field ) ) {
        return this.errors[field];
      }
      return '';

    },

    onKeyup: function( event ) {

      this.updateDisable();

      if ( this.errors.hasOwnProperty( event.target.name ) ) {
        delete this.errors[event.target.name];
      }

    },

    recordErrors: function( errors ) {
      this.errors = errors;
      this.isDisable = true;
    }

  }
});