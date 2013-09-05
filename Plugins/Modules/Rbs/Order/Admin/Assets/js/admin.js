(function () {

	"use strict";

	var app = angular.module('RbsChange');

	app.config(['$provide', function ($provide)
	{
		$provide.decorator('RbsChange.UrlManager', ['$delegate', function ($delegate)
		{
			$delegate.model('Rbs_Order')
				.route('home', 'Rbs/Order', { 'redirectTo': 'Rbs/Order/Order/'});

			$delegate.routesForModels([
				'Rbs_Order_Order',
				'Rbs_Order_Invoice'
			]);

			return $delegate;
		}]);
	}]);

	__change.createEditorForModel('Rbs_Order_Order');
	__change.createEditorForModel('Rbs_Order_Invoice');
})();