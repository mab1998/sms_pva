window.app = new Vue({
  el: '#import-clients',
  data: {
    formData: new FormData(),
    form: {
      header_exist: true
    },
    client_columns: [],
    first_name_column: null,
    last_name_column: null,
    email_address_column: null,
    user_name_column: null,
    password_column: null,
    number_column: null,
    address_column: null,
    state_column: null,
    city_column: null,
    postcode_column: null,
    country_column: null,
    sms_limit_column: null,
  },

  mounted () {

    var vm = this

    $(this.$refs.first_name_column).on('rendered.bs.select', function (e) {
      vm.first_name_column = $(this).val()
    })

    $(this.$refs.first_name_column).on('changed.bs.select', function (e) {
      vm.first_name_column = $(this).val()
    })

    $(this.$refs.last_name_column).on('rendered.bs.select', function (e) {
      vm.last_name_column = $(this).val()
    })

    $(this.$refs.last_name_column).on('changed.bs.select', function (e) {
      vm.last_name_column = $(this).val()
    })

    $(this.$refs.email_address_column).on('rendered.bs.select', function (e) {
      vm.email_address_column = $(this).val()
    })

    $(this.$refs.email_address_column).on('changed.bs.select', function (e) {
      vm.email_address_column = $(this).val()
    })

    $(this.$refs.user_name_column).on('rendered.bs.select', function (e) {
      vm.user_name_column = $(this).val()
    })

    $(this.$refs.user_name_column).on('changed.bs.select', function (e) {
      vm.user_name_column = $(this).val()
    })

    $(this.$refs.password_column).on('rendered.bs.select', function (e) {
      vm.password_column = $(this).val()
    })

    $(this.$refs.password_column).on('changed.bs.select', function (e) {
      vm.password_column = $(this).val()
    })

    $(this.$refs.number_column).on('rendered.bs.select', function (e) {
      vm.number_column = $(this).val()
    })

    $(this.$refs.number_column).on('changed.bs.select', function (e) {
      vm.number_column = $(this).val()
    })

    $(this.$refs.address_column).on('rendered.bs.select', function (e) {
      vm.address_column = $(this).val()
    })

    $(this.$refs.address_column).on('changed.bs.select', function (e) {
      vm.address_column = $(this).val()
    })

    $(this.$refs.state_column).on('rendered.bs.select', function (e) {
      vm.state_column = $(this).val()
    })

    $(this.$refs.state_column).on('changed.bs.select', function (e) {
      vm.state_column = $(this).val()
    })

    $(this.$refs.city_column).on('rendered.bs.select', function (e) {
      vm.city_column = $(this).val()
    })

    $(this.$refs.city_column).on('changed.bs.select', function (e) {
      vm.city_column = $(this).val()
    })

    $(this.$refs.postcode_column).on('rendered.bs.select', function (e) {
      vm.postcode_column = $(this).val()
    })

    $(this.$refs.postcode_column).on('changed.bs.select', function (e) {
      vm.postcode_column = $(this).val()
    })

    $(this.$refs.country_column).on('rendered.bs.select', function (e) {
      vm.country_column = $(this).val()
    })

    $(this.$refs.country_column).on('changed.bs.select', function (e) {
      vm.country_column = $(this).val()
    })

    $(this.$refs.sms_limit_column).on('rendered.bs.select', function (e) {
      vm.sms_limit_column = $(this).val()
    })

    $(this.$refs.sms_limit_column).on('changed.bs.select', function (e) {
      vm.sms_limit_column = $(this).val()
    })

  },

  methods: {

    handleImportClients: function (e) {

      var vm = this
      var name = e.target.name
      var files = e.target.files || e.dataTransfer.files

      if (!files.length) return

      vm.formData.append(name, files[0])

      this.sendRequest()

    },

    sendRequest: function () {

      var vm = this

      vm.number_column = null
      vm.client_columns = []

      for (var data in this.form) {
        vm.formData.append(data, this.form[data])
      }

      $('#loadingmessage').show()

      $.ajax({
        url: _url + '/client/get-csv-file-info',
        type: 'POST',
        data: vm.formData,
        processData: false,
        contentType: false
      })
        .done(function (response) {
          if (response && response.status == 'success') {

            $('#loadingmessage').hide()

            if (response.data.length) {
              for (var key in response.data[0]) {
                vm.client_columns.push({
                  key: key,
                  value: key
                })
              }

              var handler = setTimeout(function () {
                $(vm.$refs.first_name_column).selectpicker('refresh')
                $(vm.$refs.last_name_column).selectpicker('refresh')
                $(vm.$refs.company_column).selectpicker('refresh')
                $(vm.$refs.website_column).selectpicker('refresh')
                $(vm.$refs.email_address_column).selectpicker('refresh')
                $(vm.$refs.user_name_column).selectpicker('refresh')
                $(vm.$refs.password_column).selectpicker('refresh')
                $(vm.$refs.number_column).selectpicker('refresh')
                $(vm.$refs.address_column).selectpicker('refresh')
                $(vm.$refs.more_address_column).selectpicker('refresh')
                $(vm.$refs.state_column).selectpicker('refresh')
                $(vm.$refs.city_column).selectpicker('refresh')
                $(vm.$refs.postcode_column).selectpicker('refresh')
                $(vm.$refs.country_column).selectpicker('refresh')
                $(vm.$refs.sms_limit_column).selectpicker('refresh')
              }, 200)

            }

          } else {

            errorMessage = '<div class="alert alert-danger alert-dismissible" role="alert">\n' +
              '        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>\n' +
              response.message +
              '    </div>'

            $('.show_notification').append(errorMessage)
            $('#loadingmessage').hide()
          }
        })
      // .fail(function() {
      // 	console.log("error");
      // })
      // .always(function() {
      // 	console.log("complete");
      // });

    }

  },

  watch: {
    'form.header_exist': {
      handler: function () {

        var found = false

        for (var [key, value] of this.formData.entries()) {
          if (key && value) {
            found = true
          }
        }

        if (found) {
          this.sendRequest()
        }

      }
    }
  }

})