!function(e){var o={};function r(t){if(o[t])return o[t].exports;var n=o[t]={i:t,l:!1,exports:{}};return e[t].call(n.exports,n,n.exports,r),n.l=!0,n.exports}r.m=e,r.c=o,r.d=function(e,o,t){r.o(e,o)||Object.defineProperty(e,o,{enumerable:!0,get:t})},r.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.t=function(e,o){if(1&o&&(e=r(e)),8&o)return e;if(4&o&&"object"==typeof e&&e&&e.__esModule)return e;var t=Object.create(null);if(r.r(t),Object.defineProperty(t,"default",{enumerable:!0,value:e}),2&o&&"string"!=typeof e)for(var n in e)r.d(t,n,function(o){return e[o]}.bind(null,n));return t},r.n=function(e){var o=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(o,"a",o),o},r.o=function(e,o){return Object.prototype.hasOwnProperty.call(e,o)},r.p="/",r(r.s=579)}({579:function(e,o,r){e.exports=r(580)},580:function(e,o){window.ILABUploadToS3Storage=function(e,o,r,t){console.log(e,t);var n={action:"ilab_upload_prepare",filename:e.name,type:r};t.hasOwnProperty("uploadDirectory")&&(n.directory=t.uploadDirectory),console.log(t),jQuery.post(ajaxurl,n,function(n){if(console.log(t),"ready"==n.status){t.set({state:"uploading"});var a=new FormData;_.each(Object.keys(n.formData),function(e){"key"!=e&&a.append(e,n.formData[e])}),null!=n.cacheControl&&n.cacheControl.length>0&&a.append("Cache-Control",n.cacheControl),null!=n.expires&&a.append("Expires",n.expires),a.append("Content-Type",r),a.append("acl",n.acl),a.append("key",n.key),a.append("file",e),jQuery.ajax({url:n.url,method:"POST",contentType:!1,processData:!1,data:a,xhr:function(){var e=jQuery.ajaxSettings.xhr();return e.upload.onprogress=function(e){t.set({progress:e.loaded/e.total*100})}.bind(this),e},success:function(e){n.hasOwnProperty("key")?t.uploadFinished(n.key,o):t.uploadError()},error:function(e){t.uploadError()}})}else t.uploadError()})},"undefined"!=typeof DirectUploadItem&&(DirectUploadItem.prototype.uploadToStorage=ILABUploadToS3Storage),ilabMediaUploadItem.prototype.uploadToStorage=ILABUploadToS3Storage}});