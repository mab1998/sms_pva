window.app = new Vue({
  el: '#send-sms-file-wrapper',
  data: {
    formData: new FormData(),
    form: {
      header_exist: true
    },
    number_columns: [],
    number_column: null,
  },

  mounted () {

    var vm = this

    $(this.$refs.number_column).on('rendered.bs.select', function (e) {
      vm.number_column = $(this).val()
    })

    $(this.$refs.number_column).on('changed.bs.select', function (e) {
      vm.number_column = $(this).val()
    })

  },

  methods: {

    handleImportNumbers: function (e) {

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
      vm.number_columns = []

      for (var data in this.form) {
        vm.formData.append(data, this.form[data])
      }

      $('#loadingmessage').show()

      $.ajax({
        url: _url + '/sms/get-csv-file-info',
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
                vm.number_columns.push({
                  key: key,
                  value: key
                })
              }

              var handler = setTimeout(function () {
                $(vm.$refs.number_column).selectpicker('refresh')
                $(vm.$refs.email_address_column).selectpicker('refresh')
                $(vm.$refs.user_name_column).selectpicker('refresh')
                $(vm.$refs.company_column).selectpicker('refresh')
                $(vm.$refs.first_name_column).selectpicker('refresh')
                $(vm.$refs.last_name_column).selectpicker('refresh')
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