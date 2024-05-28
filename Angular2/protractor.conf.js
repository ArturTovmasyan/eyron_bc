/*global jasmine */
let SpecReporter = require('jasmine-spec-reporter');

exports.config = {
    allScriptsTimeout: 11000,
    seleniumAddress: 'http://test.bucketlist.loc',
    specs: [
        './e2e/**/*.e2e-spec.ts'
    ],
    capabilities: {
        'browserName': 'chrome',
        'chromeOptions' : {
            args: ['--start-maximized']
        }
    },
    directConnect: true,
    baseUrl: 'http://test.bucketlist.loc',
    framework: 'jasmine',
    jasmineNodeOpts: {
        showColors: true,
        defaultTimeoutInterval: 30000,
        isVerbose : true,
        print: function() {}
    },
    useAllAngular2AppRoots: true,
    beforeLaunch: function() {
        require('ts-node').register({
            project: 'e2e'
        });
    },
    onPrepare: function() {
        jasmine.getEnv().addReporter(new SpecReporter());
        browser.ignoreSynchronization = false;
    }
};
