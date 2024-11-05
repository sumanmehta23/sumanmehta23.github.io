var bgfx = angular.module("bridgingfx", ['ngRoute', 'ngFileUpload']);
var config = {
	"base": "files/_core/controller/route.php",
	"country_url": "files/assets/js/country.js"
};

/**
 * Angular Controllers
 */
bgfx.controller('login', ['$scope', '$http', '$window', '$timeout', function ($scope, $http, $window, $timeout) {
	$scope.login = function (username, password) {
		let data = {
			"email": username,
			"password": password,
			"request": "login"
		};
		$http.post(config.base, data).then(function (r) {
			$scope.message = r.data.message;
			console.log(r.data);
			if (r.data.status === "success")
				$window.location.href = "dashboard.php";
		});
	};
	$scope.ButtonText = "LOGIN";
	$scope.test = "true";

	$scope.search = function () {
		$scope.test = "false";
		$scope.ButtonText = "Loading";
		// Do your searching here

		$timeout(function () {
			$scope.ButtonText = "LOGIN";
		}, 300);
	};
}]);

bgfx.controller('register', ['$scope', '$http', '$window', function ($scope, $http, $window) {
	$scope.btn_disable = false;
	$scope.tc = true;
	$scope.tc_checked = true;
	var strength = 0;

	$scope.changetc = function (tc) {
		$scope.btn_disable = !(tc);
	}

	$http.get(config.country_url).then(function (r) {
		$scope.countryList = r.data;
	});

	$scope.updateDialcode = function (country) {
		$scope.dialcode = country.dial_code;
	}

	$scope.checkconfirmpassword = function (password, cpassword) {
		if (password !== cpassword) {
			$scope.cPassword_status = true;
			$scope.cPassword_message = "password mismatch";
		} else {
			$scope.cPassword_status = false;
			$scope.cPassword_message = "";
		}
	}

	$scope.register_user = function (name, email, cpassword, country, dialcode, number) {
		let ref_code = $("#ref").val();
		let ref_code_level = $("#ref_level").val();
		$scope.strength = strength;
		let data = {
			"email": email,
			"name": name,
			"password": cpassword,
			"country": country.name,
			"dialcode": dialcode,
			"number": number,
			"ref_code": ref_code || 'noIB',
			"ref_code_level": ref_code_level || 'no_code',
			"request": "register"
		}
		if (strength > 30) {
			$http.post(config.base, data).then(function (r) {
				if (r.data.status === "success") {
					swal.fire({
						type: "success",
						text: r.data.message,
						confirmButtonColor: '#3085d6',
						confirmButtonText: 'Verify Email Now'
					}).then((result) => {
						if (result.value) {
							$window.location.href = "eVerification.php";
						}
					});
				} else {
					swal.fire({
						type: "error",
						text: r.data.message
					});
				}
			});
		} else {
			$scope.password_strength = "enter a strong password";
			swal.fire({
				type: "warning",
				text: "enter a strong password"
			});
		}
	}

	$scope.checkPassword = function (password) {
		strength = PasswordStrength(password);
		if (strength > 80)
			$scope.password_strength = "Password Strong";
		else if (strength > 60)
			$scope.password_strength = "Password Good";
		else if (strength > 30)
			$scope.password_strength = "Password Weak";
		else
			$scope.password_strength = "enter a strong password";
	}

}]);

bgfx.controller('passwordReset', ['$scope', '$http', '$window', function ($scope, $http, $window) {
	$scope.password_strength = "enter a strong password";
	$scope.strength = 0;

	$scope.checkPassword = function (password) {
		strength = PasswordStrength(password);
		if (strength > 80)
			$scope.password_strength = "Password Strong";
		else if (strength > 60)
			$scope.password_strength = "Password Good";
		else if (strength > 30)
			$scope.password_strength = "Password Weak";
		else
			$scope.password_strength = "enter a strong password";
		$scope.strength = strength;
	}

	$scope.reset = function (email, token, password) {
		let data = {
			"request": "reset_password_token",
			"email": email,
			"token": token,
			"password": password
		};
		$http.post(config.base, data).then(function (r) {
			if (r.data.status === "success") {
				swal.fire({
					type: r.data.status,
					text: r.data.message,
					confirmButtonColor: '#3085d6',
					confirmButtonText: 'Login Now'
				}).then((result) => {
					if (result.value) {
						$window.location.href = "login.php";
					}
				});
			} else {
				swal.fire({
					type: r.data.status,
					text: r.data.message
				});
			}
			$scope.token = "";
			$scope.email = "";
			$scope.password = "";
		});
	}

	$scope.resetRequest = function (email) {
		$http.post(config.base, {
			"request": "password_rest_request",
			"email": email
		}).then(function (r) {
			swal.fire({
				type: r.data.status,
				text: r.data.message
			});
			$scope.remail = "";
		});
	}

}]);

bgfx.controller('mainView', ['$scope', '$timeout', function ($scope, $timeout) {
	$scope.selected = 1;
	$scope.selectMenu = function (menu) {
		$scope.selected = menu;
	}
	$('#sidebarCollapse').on('click', function () {
		$('#sidebar').toggleClass('active');
	});
}]);

bgfx.controller('dashboard', ['$scope', '$http', '$timeout', function ($scope, $http, $timeout) {
	$scope.live = [];
	$scope.demo = [];
	liveaccounts();
	demoaccounts();
	GetUser();

	$scope.deposit_history = [];
	$scope.withdraw_history = [];
	get_chart_history();

	function get_chart_history() {
		let data = {
			'request': 'get_deposit_withdraw_chart_history'
		};
		$http.post(config.base, data).then(function (r) {
			$scope.deposit_history = r.data.data.deposit;
			$scope.withdraw_history = r.data.data.withdraw;

			update_chart($scope.deposit_history, $scope.withdraw_history);
		});
	}

	function update_chart(deposit_h, withdraw_h) {
		function createChart() {
			Highcharts.stockChart('container', {
				rangeSelector: {
					buttons: [{
						type: 'week',
						count: 0,
						text: '1w'
					}, {
						type: 'month',
						count: 1,
						text: '1m'
					}, {
						type: 'month',
						count: 3,
						text: '3m'
					}, {
						type: 'month',
						count: 6,
						text: '6m'
					}, {
						type: 'year',
						count: 1,
						text: '1y'
					}],
					selected: 0
				},
				subtitle: {
					text: 'Deposit and Withdraw History'
				},

				xAxis: {
					type: 'datetime',
					gridLineWidth: 0,
					dateTimeLabelFormats: {
						month: '%e. %b',
						year: '%b'
					},
					title: {
						text: 'Date'
					},
					events: {
						afterSetExtremes: afterSetExtremes
					},
					minRange: 3600 * 1000
				},
				yAxis: {
					opposite: false,
					title: {
						text: 'Deposit and Withdraw'
					},
					min: 0
				},
				scrollbar: {
					enabled: false
				},
				credits: {
					enabled: false
				},
				navigator: {
					enabled: false
				},
				series: [{
					name: 'Deposit',
					data: process_to_array(deposit_h)
				}, {
					name: 'Withdraw',
					data: process_to_array(withdraw_h)
				}]

			}, function (chart) {
				setTimeout(function () {
					$('input.highcharts-range-selector', $(chart.container).parent());
				}, 5000);
			});
		};

		function afterSetExtremes() {
			var chart = Highcharts.charts[0];
			chart.showLoading('<i class="fa fa-spinner fa-spin" style="font-size:48px;color:red"></i>');
			$timeout(function () {
				chart.hideLoading();
			}, 300);
		};

		createChart();

		function process_to_array(data) {
			let arr = [];
			for (i = 0; i < data.length; i++) {
				arr.push([(data[i].date) * 1000, parseFloat(data[i].amount)]);
			}
			return arr;
		}

		function toTimestamp(strDate) {
			var datum = Date.parse(strDate);
			return datum / 1000;
		}
	}

	function GetUser() {
		let data = {
			"request": "GetUserDetails"
		};
		$http.post(config.base, data).then(function (r) {
			$scope.user_details = r.data.data;
		});
	}

	function liveaccounts() {
		let data = {
			"request": "LiveAccounts"
		};
		$http.post(config.base, data).then(function (r) {
			$scope.live = angular.fromJson(r.data.data);
		});
	}

	function demoaccounts() {
		let data = {
			"request": "DemoAccounts"
		};
		$http.post(config.base, data).then(function (r) {
			$scope.demo = angular.fromJson(r.data.data);
		});
	}

	$scope.account_setting = function (account, type) {
		$("#account_setting").modal('show');
		$scope.account = account;
	}

	$scope.updateTradePwd = function (login, password, type) {
		let data = {
			"request": "update_trade_password",
			"login": login,
			"password": password,
			"type": type
		};
		$http.post(config.base, data).then(function (r) {
			if (r.data.status === "success")
				swal.fire({
					type: "success",
					text: r.data.message
				});

			if (r.data.status === "error")
				swal.fire({
					type: "error",
					text: r.data.message
				});
			$scope.tpassword = "";
		});
	}

	$scope.updateInvestorPwd = function (login, password, type) {
		let data = {
			"request": "update_investor_password",
			"login": login,
			"password": password,
			"type": type
		};
		$http.post(config.base, data).then(function (r) {
			if (r.data.status === "success")
				swal.fire({
					type: "success",
					text: r.data.message
				});

			if (r.data.status === "error")
				swal.fire({
					type: "error",
					text: r.data.message
				});
			$scope.ipassword = "";
		});
	}
}]);

bgfx.controller('profile', ['$scope', '$http', function ($scope, $http) {
	$scope.timerOn = false;

	GetUser();
	$http.get(config.country_url).then(function (r) {
		$scope.countryList = r.data;
	});

	$scope.updateDialcode = function (country) {
		$scope.dialcode = country.dial_code;
	}

	function GetUser() {
		let data = {
			"request": "GetUserDetails"
		};
		$http.post(config.base, data).then(function (r) {
			let user = r.data.data;
			$scope.username = user.name;
			$scope.email = user.email;
			$scope.dial_code = user.dial_code;
			$scope.number = user.number;
			$scope.country = user.country;
			$scope.address = user.address;
			$scope.state = user.state;
			$scope.city = user.city;
			$scope.zipcode = user.zipcode;
		});
	}

	$scope.openModal = function (otp_number, type) {
		if (type === "verify") {
			$scope.otp_number = otp_number;
			$("#verifyNumber").modal('show');
		}
		$scope.timerOn = true;
		$scope.timer(300);
	}

	$scope.timer = function (remaining) {
		var m = Math.floor(remaining / 60);
		var s = remaining % 60;

		s = s < 10 ? '0' + s : s;
		$("#timer").html(m + ':' + s);
		remaining -= 1;
		if (remaining >= 0 && $scope.timerOn) {
			setTimeout(function () {
				$scope.timer(remaining);
			}, 1000);
		}
	}

	$scope.resend_otp = function () {
		$scope.timerOn = true;
		$scope.timer(300);
	}

	function MyCntrl($scope) {
		$scope.verify_number = false;

		$scope.isVerify = function (verify_number) {
			if (verify_number)
				$scope.verify_number = false;
			else
				$scope.verify_number = true;
		}
	}
	MyCntrl($scope);


	$scope.updateProfile = function (number, address, state, city, country, zipcode) {
		if (typeof (country) === 'object') {
			country = country.name;
			dial_code = country.dial_code;
		} else {
			country = $scope.country;
			dial_code = $scope.dial_code;
		}
		let data = {
			"request": "update_profile",
			"number": number,
			"state": state,
			"city": city,
			"address": address,
			"dial_code": dial_code,
			"country": country,
			"zipcode": zipcode
		};
		$http.post(config.base, data).then(function (r) {
			if (r.data.status === "success") {
				swal.fire({
					type: 'success',
					title: '',
					text: r.data.message
				});
				GetUser();
			} else {
				swal.fire({
					type: 'error',
					title: '',
					text: r.data.message
				});
			}
		});
	}
}]);

bgfx.controller('live', ['$scope', '$http', function ($scope, $http) {
	$scope.types = [];
	$scope.leverages = [1, 25, 50, 100, 200, 300, 400, 500];
	$scope.disabled = false;
	account_types();

	function account_types() {
		let data = {
			"request": "AccountTypesLive"
		};
		$http.post(config.base, data).then(function (r) {
			$scope.types = angular.fromJson(r.data.data);
		});
	}

	$scope.updateLeverage = function (leverage) {
		if (leverage === 100)
			$scope.leverages = [1, 25, 50, 100];
		if (leverage === 200)
			$scope.leverages = [1, 25, 50, 100, 200];
		if (leverage === 300)
			$scope.leverages = [1, 25, 50, 100, 200, 300];
		if (leverage === 400)
			$scope.leverages = [1, 25, 50, 100, 200, 300, 400];
		if (leverage === 500)
			$scope.leverages = [1, 25, 50, 100, 200, 300, 400, 500];
	}

	$scope.createLiveAccount = function (name, leverage) {
		$scope.disabled = true;
		let data = {
			"request": "createLiveAccount",
			"type": name,
			"leverage": leverage
		};
		$http.post(config.base, data).then(function (r) {
			$scope.disabled = false;
			swal.fire({
				type: r.data.status,
				text: r.data.message
			});
			$scope.acc_type = "";
			$scope.leverage = "";
		});
	}
}]);

bgfx.controller('demo', ['$scope', '$http', function ($scope, $http) {
	$scope.types = [];
	$scope.leverages = [1, 25, 50, 100, 200, 300, 400, 500];
	$scope.deposits = [1000, 2000, 3000, 5000, 10000, 50000, 100000];
	account_types();

	function account_types() {
		let data = {
			"request": "AccountTypesDemo"
		};
		$http.post(config.base, data).then(function (r) {
			$scope.types = angular.fromJson(r.data.data);
		});
	}

	$scope.CreateDemoAccount = function (name, leverage, deposit) {
		let data = {
			"request": "createDemoAccount",
			"type": name,
			"leverage": leverage,
			"amount": deposit
		};
		$http.post(config.base, data).then(function (r) {
			swal.fire({
				type: r.data.status,
				text: r.data.message
			});
			$scope.acc_type = "";
			$scope.leverage = "";
			$scope.deposit = "";
		});
	}
}]);

bgfx.controller('verification', ['$scope', '$http', 'Upload', function ($scope, $http, Upload) {
	$scope.docs = [];
	GetDocs();

	$scope.startDate = new Date('01/01/19');
	$scope.endDate = new Date();

	$scope.currentPage = 0;
	$scope.itemsPerPage = 10;
	$scope.pagingOptions = [10, 25, 50, 100];
	$scope.totalItems = $scope.docs.length;
	$scope.pages = function () {
		return Math.ceil($scope.docs.length / $scope.itemsPerPage);
	};

	$scope.uploadID = function () {
		if ($scope.id.file.$valid && $scope.id_file) {
			$scope.upload($scope.id_file, 'id', $scope.pnote);
		}
	};

	$scope.uploadAddress = function () {
		if ($scope.address.file.$valid && $scope.address_file) {
			$scope.upload($scope.address_file, 'address', $scope.anote);
		}
	};

	$scope.upload = function (file, type, note) {
		Upload.upload({
			url: config.base,
			data: {
				file: file,
				'note': note || '',
				'request': 'UploadDoc',
				'type': type
			}
		}).then(function (resp) {
			if (type === "id") {
				$scope.pnote = '';
				$scope.id_file = '';
			}
			if (type === "address") {
				$scope.anote = '';
				$scope.address_file = '';
			}
			Swal.fire({
				type: resp.data.status,
				text: resp.data.message
			});
			if (resp.data.status === "success") {
				GetDocs();
			}
		}, function (resp) {
			Swal.fire({
				type: "error",
				title: "",
				text: "Error : File Upload " + resp.data.message
			});
		}, function (evt) {
			var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
			console.log('progress: ' + progressPercentage + '% ' + evt.config.data.file.name);
		});
	};

	function GetDocs() {
		$http.post(config.base, {
			"request": "GetUserDocs"
		}).then(function (r) {
			$scope.docs = r.data.data;
		});
	}
}]);

bgfx.controller('eVerification', ['$scope', '$http', function ($scope, $http) {
	$scope.emailVerify = function (token, email) {
		let data = {
			"request": "email_verification",
			"token": token,
			"email": email
		};
		$http.post(config.base, data).then(function (r) {
			swal.fire({
				type: r.data.status,
				text: r.data.message
			});
			$scope.token = "";
			$scope.email = "";
		});
	}
}]);

bgfx.controller('setting', ['$scope', '$http', function ($scope, $http) {
	$scope.password_strength = "enter a strong password";
	$scope.ex_password = "(ex: Example@1234$)"

	$scope.updatePassword = function (password) {
		$http.post(config.base, {
			"request": "Update_Password",
			"password": password
		}).then(function (r) {
			if (r.data.status === "success") {
				swal.fire({
					type: "success",
					text: r.data.message
				});
			} else {
				swal.fire({
					type: "error",
					text: r.data.message
				});
			}
		});
	}

	$scope.checkPassword = function (password) {
		strength = PasswordStrength(password);
		if (strength > 80)
			$scope.password_strength = "Password Strong";
		else if (strength > 60)
			$scope.password_strength = "Password Good";
		else if (strength > 30)
			$scope.password_strength = "Password Weak";
		else
			$scope.password_strength = "enter a strong password";
	}

	$scope.checkconfirmpassword = function (password, cpassword) {
		if (password !== cpassword) {
			$scope.cPassword_status = true;
			$scope.cPassword_message = "password mismatch";
		} else {
			$scope.cPassword_status = false;
			$scope.cPassword_message = "";
		}
	}
}]);

bgfx.controller('finance', ['$scope', '$http', 'Upload', function ($scope, $http, Upload) {
	liveaccounts();
	deposiList();
	$scope.deposit = [];
	$scope.startDate = new Date('01/01/19');
	$scope.endDate = new Date();

	$scope.currentPage = 0;
	$scope.itemsPerPage = 5;
	$scope.pagingOptions = [5, 10, 25, 50, 100];
	$scope.totalItems = $scope.deposit.length;
	$scope.pages = function () {
		return Math.ceil($scope.deposit.length / $scope.itemsPerPage);
	};


	function liveaccounts() {
		let data = {
			"request": "LiveAccounts"
		};
		$http.post(config.base, data).then(function (r) {
			$scope.live = angular.fromJson(r.data.data);
		});
	}

	function deposiList() {
		let data = {
			"request": "GetDepositDetails"
		};
		$http.post(config.base, data).then(function (r) {
			$scope.deposit = angular.fromJson(r.data.data);
		});
	}

	$scope.DepositManual = function () {
		if ($scope.file) {
			$scope.upload($scope.file, $scope.acc_number, $scope.amount, $scope.deposit_type);
		}
	}

	$scope.upload = function (file, login, amount, dtype) {
		Upload.upload({
			url: config.base,
			data: {
				file: file,
				'request': 'manual_deposit',
				'login': login['login'],
				'amount': amount,
				'type': dtype
			}
		}).then(function (resp) {
			if (resp.data.status === "success") {
				swal.fire({
					type: "success",
					title: "",
					text: "Deposit Done, " + resp.data.message
				});
				$scope.acc_number = "";
				$scope.amount = "";
				$scope.deposit_type = "";
				$scope.file = "";
				deposiList();
			}
			if (resp.data.status === "error") {
				Swal.fire({
					type: "error",
					title: "",
					text: "Deposit Failed, " + resp.data.message
				});
			}
		}, function (resp) {
			Swal.fire({
				type: "error",
				title: "",
				text: "Deposit Failed, " + resp.data.message
			});
		}, function (evt) {
			var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
			console.log('progress: ' + progressPercentage + '% ' + evt.config.data.file.name);
		});
	};

	// eWallet Functions
	$scope.open_ewallet_modal = function (eWallet, url) {
		$scope.selected_type = eWallet;
		$scope.eWallet_url = url;
		$("#eWallet").modal('show');
	}

	$scope.CloseModal = function () {
		$("#eWallet").modal('hide');
	}
}]);

bgfx.controller('pm_controller', ['$scope', '$http', '$routeParams', function ($scope, $http, $routeParams) {
	$scope.login = $routeParams.login;
	$scope.amount = $routeParams.amount;
}]);

bgfx.controller('gp_controller', ['$scope', '$http', '$routeParams', function ($scope, $http, $routeParams) {
	$scope.login = $routeParams.login;
	$scope.amount = $routeParams.amount;
}]);

bgfx.controller('bitcoinpay_controller', ['$scope', '$http', '$routeParams', function ($scope, $http, $routeParams) {
	$scope.login = $routeParams.login;
	$scope.amount = $routeParams.amount;
}]);

bgfx.controller('creditcard_controller', ['$scope', '$http', '$routeParams', function ($scope, $http, $routeParams) {
	$scope.login = $routeParams.login;
	$scope.amount = $routeParams.amount;
}]);

bgfx.controller('withdraw', ['$scope', '$http', function ($scope, $http) {
	withdrawList();
	liveaccounts();
	$scope.withdraw = [];
	$scope.startDate = new Date('01/01/19');
	$scope.endDate = new Date();
	$scope.currentPage = 0;
	$scope.itemsPerPage = 5;
	$scope.pagingOptions = [5, 10, 25, 50, 100];
	$scope.totalItems = $scope.withdraw.length;
	$scope.pages = function () {
		return Math.ceil($scope.withdraw.length / $scope.itemsPerPage);
	};

	function liveaccounts() {
		let data = {
			"request": "LiveAccounts"
		};
		$http.post(config.base, data).then(function (r) {
			$scope.live = angular.fromJson(r.data.data);
		});
	}

	function withdrawList() {
		let data = {
			"request": "GetWithdrawDetails"
		};
		$http.post(config.base, data).then(function (r) {
			$scope.withdraw = angular.fromJson(r.data.data);

		});
	}

	$scope.accountCheck = function (login) {
		let data = {
			"request": "GetAccountDetails",
			"login": login,
			"type": "live"
		};
		$http.post(config.base, data).then(function (r) {
			$scope.account_details = angular.fromJson(r.data.data);
		});
	}

	$scope.WithdrawAmount = function (login, amount, details) {
		$scope.disabled = true;
		let data = {
			"request": "withdraw",
			"login": login,
			"amount": amount,
			"detail": details
		};
		$http.post(config.base, data).then(function (r) {
			$scope.disabled = false;
			swal.fire({
				type: r.data.status,
				text: r.data.message
			});
			if (r.data.status === "success")
				withdrawList();
		});
	}
}]);

bgfx.controller('report', ['$scope', '$http', 'Excel', '$timeout', function ($scope, $http, Excel, $timeout) {
	withdrawList();
	deposiList();
	$scope.deposit = [];
	$scope.current_page = 0;
	$scope.items_per_page = 5;
	$scope.paging_options = [5, 10, 25, 50, 100];
	$scope.total_items = $scope.deposit.length;
	$scope.pages = function () {
		return Math.ceil($scope.deposit.length / $scope.items_per_page);
	};

	$scope.start_date = new Date('01/01/19');
	$scope.end_date = new Date();

	$scope.withdraw = [];
	$scope.current_page_1 = 0;
	$scope.items_per_page_1 = 5;
	$scope.paging_options_1 = [5, 10, 25, 50, 100];
	$scope.total_items_1 = $scope.withdraw.length;
	$scope.pages_1 = function () {
		return Math.ceil($scope.withdraw.length / $scope.items_per_page_1);
	};

	$scope.start_date_1 = new Date('01/01/19');
	$scope.end_date_1 = new Date();


	function withdrawList() {
		let data = {
			"request": "GetWithdrawDetails"
		};
		$http.post(config.base, data).then(function (r) {
			$scope.withdraw = angular.fromJson(r.data.data);
		});
	}

	function deposiList() {
		let data = {
			"request": "GetDepositDetails"
		};
		$http.post(config.base, data).then(function (r) {
			$scope.deposit = angular.fromJson(r.data.data);
		});
	}

	$scope.exportToExcel = function (tableId) {
		var exportHref = Excel.tableToExcel(tableId, 'WireWorkbenchDataExport');
		$timeout(function () {
			location.href = exportHref;
		}, 100);
	}
}]);

/**
 * PAMM Controller Start
 */

bgfx.controller('pamm_invest', ['$scope', '$http', function ($scope, $http) {
	$scope.pamm_managers = [];
	$scope.processing = false;

	get_pamm_managers();
	get_trading_accounts();

	function get_pamm_managers() {
		let data = {
			"request": "get_pamm_managers"
		};
		$http.post(config.base, data).then(function (r) {
			$scope.pamm_managers = r.data.data;
		});
	}

	$scope.open_modal = function (pamm_gi) {
		$scope.modal_pamm_invest = pamm_gi;
		$("#pamm_invest_modal").modal('show');
	}

	function get_trading_accounts() {
		let data = {
			'request': 'get_trade_accounts_for_invest'
		};
		$http.post(config.base, data).then(function (r) {
			$scope.trading_accounts = r.data.data;
		});
	}

	$scope.request_pamm_investment = function (invest_uid, login) {
		$scope.processing = true;
		let data = {
			"request": "register_new_pamm_investment",
			"invest_uid": invest_uid,
			"login": login,
		};
		$http.post(config.base, data).then(function (r) {
			swal.fire({
				"type": r.data.status,
				"text": r.data.message
			});
			$scope.processing = false;
			if (r.data.status === "success") {
				$("#pamm_invest_modal").modal('hide');
				get_trading_accounts();
			}
		});
	}
}]);

bgfx.controller('pamm_manager', ['$scope', '$http', function ($scope, $http) {
	$scope.trading_accounts = [];
	$scope.pamm_accounts = [];

	get_trading_accounts();
	get_pamm_accounts();

	function get_trading_accounts() {
		let data = {
			'request': 'get_trade_accounts_for_invest'
		};
		$http.post(config.base, data).then(function (r) {
			$scope.trading_accounts = r.data.data;
		});
	}

	function get_pamm_accounts() {
		let data = {
			'request': 'get_pamm_accounts'
		};
		$http.post(config.base, data).then(function (r) {
			$scope.pamm_accounts = r.data.data;
		});
	}

	$scope.request_pamm_account = function (login, name, success_fee, min_deposit) {
		let data = {
			'request': 'request_pamm_account',
			'login': login,
			'name': name,
			'success_fee': success_fee,
			'min_deposit': min_deposit
		};
		$http.post(config.base, data).then(function (r) {
			swal.fire({
				type: r.data.status,
				text: r.data.message
			});

			if (r.data.status === "success") {
				get_trading_accounts();
				get_pamm_accounts();
			}
		});
	}

}]);

bgfx.controller('pamm_report', ['$scope', '$http', function ($scope, $http) {
	$scope.pamm = [];
	$scope.processing = false;
	get_pamm_sub_accounts();

	function get_pamm_sub_accounts() {
		let data = {
			'request': 'get_pamm_sub_accounts'
		};
		$http.post(config.base, data).then(function (r) {
			$scope.pamm = r.data.data;
		});
	}

	$scope.cancel_pamm_investment = function (order_uid) {
		$scope.processing = true;
		let data = {
			"request": "cancel_pamm_investment",
			"order_uid": order_uid,
		};
		$http.post(config.base, data).then(function (r) {
			swal.fire({
				"type": r.data.status,
				"text": r.data.message
			});
			if (r.data.status === "success") {
				get_pamm_sub_accounts();
			}
			$scope.processing = false;
		});
	}
}]);

bgfx.controller('pamm_manager_detail_analysis', ['$scope', '$http', '$routeParams', function ($scope, $http, $routeParams) {

	function ctrl($scope) {

		$scope.toggle = function () {
			$scope.state = !$scope.state;
		};
	}
	ctrl($scope);
	Highcharts.chart('container', {

		title: {
			text: 'Profit and Withdrawl'
		},
		yAxis: {
			title: {
				text: 'Profit And Loss'
			}
		},

		series: [{
			name: 'Deposit',
			data: [43934, 52503, 57177, 69658, 97031, 119931, 137133, 154175]
		}, {
			name: 'Equity',
			data: [24916, 24064, 29742, 29851, 32490, 30282, 38121, 40434]
		}],
	});
	Highcharts.chart('columnContainer', {
		chart: {
			type: 'column'
		},
		title: {
			text: 'Deposit And Withdrawl'
		},
		xAxis: {
			categories: [
				'Jan',
				'Feb',
				'Mar',
				'Apr',
				'May',
				'Jun',
				'Jul',
				'Aug',
				'Sep',
				'Oct',
				'Nov',
				'Dec'
			],
			crosshair: true
		},
		yAxis: {
			min: 0,
			title: {
				text: 'profit and Equity'
			}
		},
		series: [{
			name: 'Withdrawl',
			data: [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4]

		}]
	});
	Highcharts.chart('pieContainer', {
		chart: {
			plotBackgroundColor: null,
			plotBorderWidth: null,
			plotShadow: false,
			type: 'pie'
		},
		title: {
			text: 'Browser market shares in January, 2019'
		},
		tooltip: {
			pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
		},
		series: [{
			name: 'Brands',
			colorByPoint: true,
			data: [{
				name: 'Chrome',
				y: 61.41,
				sliced: true,
				selected: true
			}, {
				name: 'Internet Explorer',
				y: 11.84
			}, {
				name: 'Firefox',
				y: 10.85
			}, {
				name: 'Edge',
				y: 4.67
			}, {
				name: 'Safari',
				y: 4.18
			}, {
				name: 'Sogou Explorer',
				y: 1.64
			}, {
				name: 'Opera',
				y: 1.6
			}, {
				name: 'QQ',
				y: 1.2
			}, {
				name: 'Other',
				y: 2.61
			}]
		}]
	});
}]);

bgfx.controller('pamm_investment_report', ['$scope', '$http', function ($scope, $http) {
	$scope.pamm = [];
	pamm_investment_accounts();

	function pamm_investment_accounts() {
		let data = {
			'request': 'get_manager_investment_accounts'
		};
		$http.post(config.base, data).then(function (r) {
			$scope.pamm = r.data.data;
		});
	}

	$scope.approve_pamm_investment = function (uid, manager_login, client_login) {
		let data = {
			'request': 'approve_pamm_investment',
			'uid': uid,
			'manager_login': manager_login,
			'client_login': client_login
		};
		$http.post(config.base, data).then(function (r) {
			swal.fire({
				'type': r.data.status,
				'text': r.data.message
			});
			if (r.data.status === "success") {
				pamm_investment_accounts();
			}
		});
	}

	$scope.reject_pamm_investment = function (uid, manager_login, client_login) {
		let data = {
			'request': 'reject_pamm_investment',
			'uid': uid,
			'manager_login': manager_login,
			'client_login': client_login
		};
		$http.post(config.base, data).then(function (r) {
			swal.fire({
				'type': r.data.status,
				'text': r.data.message
			});
			if (r.data.status === "success") {
				pamm_investment_accounts();
			}
		});
	}
}]);

/**
 * PAMM Controller End
 */

/**
 * General Investment Controller Start
 */

bgfx.controller('general_investment', ['$scope', '$http', function ($scope, $http) {
	$scope.general_investment = [];
	$scope.trading_accounts = [];
	$scope.processing = false;

	get_investment_plans();
	get_trading_accounts();

	function get_investment_plans() {
		let data = {
			"request": "get_general_investments"
		};
		$http.post(config.base, data).then(function (r) {
			$scope.general_investment = r.data.data;
		});
	}

	$scope.open_modal = function (gi) {
		$scope.modal_invest = gi;
		$("#invest_modal").modal('show');
	}

	function get_trading_accounts() {
		let data = {
			'request': 'get_trade_accounts_for_invest'
		};
		$http.post(config.base, data).then(function (r) {
			$scope.trading_accounts = r.data.data;
		});
	}

	$scope.request_investment = function (invest_uid, login) {
		$scope.processing = true;
		let data = {
			"request": "register_new_investment",
			"invest_uid": invest_uid,
			"login": login,
		};
		$http.post(config.base, data).then(function (r) {
			swal.fire({
				"type": r.data.status,
				"text": r.data.message
			});
			$scope.processing = false;
			if (r.data.status === "success") {
				get_investment_plans();
				get_trading_accounts();
				$("#invest_modal").modal('hide');
			}
		});
	}
}]);

bgfx.controller('investment_report', ['$scope', '$http', function ($scope, $http) {
	$scope.investments = [];
	get_investments();

	function get_investments() {
		let data = {
			"request": "get_investments",
		};
		$http.post(config.base, data).then(function (r) {
			$scope.investments = r.data.data;
		});
	}

	$scope.cancel_investment = function (order_uid) {
		let data = {
			"request": "cancel_investment",
			"order_uid": order_uid,
		};
		$http.post(config.base, data).then(function (r) {
			swal.fire({
				"type": r.data.status,
				"text": r.data.message
			});
			if (r.data.status === "success") {
				get_investments();
			}
		});
	}
}]);

/**
 * General Investment Controller End
 */

/*------------------------------- Global Custom Function -------------------------------*/
function PasswordStrength(pass) {
	var score = 0;
	if (!pass)
		return score;

	// award every unique letter until 5 repetitions
	var letters = new Object();
	for (var i = 0; i < pass.length; i++) {
		letters[pass[i]] = (letters[pass[i]] || 0) + 1;
		score += 5.0 / letters[pass[i]];
	}

	// bonus points for mixing it up
	var variations = {
		digits: /\d/.test(pass),
		lower: /[a-z]/.test(pass),
		upper: /[A-Z]/.test(pass),
		nonWords: /\W/.test(pass),
	}

	variationCount = 0;
	for (var check in variations) {
		variationCount += (variations[check] == true) ? 1 : 0;
	}
	score += (variationCount - 1) * 10;

	return parseInt(score);
}

function daysCalc() {
	var startDate = new Date("2016-08-01");
	var endDate = new Date();
	var oneDay = 24 * 60 * 60 * 1000;
	var diffDays = Math.round(Math.abs((startDate.getTime() - endDate.getTime()) / (oneDay)));
	return diffDays;
}

/*------------------------------- Angular Factory ------------------------------------------------*/

bgfx.factory('Excel', function ($window) {
	var uri = 'data:application/vnd.ms-excel;base64,',
		template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>',
		base64 = function (s) {
			return $window.btoa(unescape(encodeURIComponent(s)));
		},
		format = function (s, c) {
			return s.replace(/{(\w+)}/g, function (m, p) {
				return c[p];
			})
		};
	return {
		tableToExcel: function (tableId, worksheetName) {
			var table = $(tableId),
				ctx = {
					worksheet: worksheetName,
					table: table.html()
				},
				href = uri + base64(format(template, ctx));
			return href;
		}
	};
});

/***************** Angular Filter Custom Functions *********************/

/* Date Filter */

bgfx.filter("date_filter", function ($filter) {
	return function (data, sDate, eDate, date_param) {
		return $filter('filter')(data, date_param, function (v) {
			var date = moment(v);
			return date >= moment(sDate) && date <= moment(eDate);
		});
	};
});


/* Unique Filter */

bgfx.filter('unique', function () {
	return function (collection, keyname) {
		var output = [],
			keys = [];

		angular.forEach(collection, function (item) {
			var key = item[keyname];
			if (keys.indexOf(key) === -1) {
				keys.push(key);
				output.push(item);
			}
		});
		return output;
	};
});

/* Pagination Filter */

bgfx.filter('start_from', function ($filter) {
	return function (input, start) {
		if (!input || !input.length) {
			return;
		}
		start = +start; //parse to int
		return input.slice(start);

	}
});

/* Email filter */

bgfx.filter("email_filter", function ($filter) {
	return function (user_email) {
		if (user_email) {
			var avg, split, a, b = "";
			split = user_email.split("@");
			a = split[0];
			avg = a.length / 2;
			a = a.substring(a, (a.length - avg));
			b = split[1];
			return a + "******@" + b;
		}
		return user_email;
	};
});

/* Mobile Number filter */


bgfx.filter("number_filter", function ($filter) {
	return function (number) {
		if (number) {
			var a, b = "";
			a = number.substr(0, 4);
			b = number.substr(6, 4);
			return a + "******" + b;
		}
		return number;
	}
});