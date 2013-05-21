(function () {

	"use strict";

	function editorChangeThemePageTemplate (Editor) {

		return {
			restrict : 'EC',

			templateUrl : 'Change/Theme/PageTemplate/editor.twig',

			replace : true,

			// Create isolated scope
			scope: {
				original: '=document',
				onSave: '&',
				onCancel: '&',
				section: '='
			},

			link : function (scope, elm) {
				Editor.initScope(scope, elm);
			}
		};

	}

	editorChangeThemePageTemplate.$inject = ['RbsChange.Editor'];

	angular.module('RbsChange').directive('editorChangeThemePageTemplate', editorChangeThemePageTemplate);

})();