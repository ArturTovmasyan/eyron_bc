'use strict';

angular.module('trans', ['Interpolation',
    'pascalprecht.translate', 'Components'])
    .config(['$translateProvider', 'UserContext' , function ($translateProvider, UserContext) {

        var locale = UserContext.locale;

        $translateProvider.useStaticFilesLoader({
            prefix: '/bundles/app/trans/messages.',
            suffix: '.json'
        });

        $translateProvider.preferredLanguage(locale);

    }]);
