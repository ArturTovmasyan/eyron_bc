/**
 * Created by andranik on 7/13/16.
 */

var cliargs = require('cliargs');
var fs = require('fs');
var path = require('path');
var gm = require('gm');

var argsObj = cliargs.parse();

if(!argsObj.f || !argsObj.r || !argsObj.t){
    console.log('invalid arguments');
    return;
}

gm(argsObj.f)
    .setFormat("jpg")
    .write(argsObj.r + argsObj.t, function (err) {
        if (err){
            console.log(err);
            return;
        }
        console.log('ok');
    });
return;