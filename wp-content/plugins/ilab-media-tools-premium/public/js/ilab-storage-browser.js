!function(e){var t={};function n(o){if(t[o])return t[o].exports;var r=t[o]={i:o,l:!1,exports:{}};return e[o].call(r.exports,r,r.exports,n),r.l=!0,r.exports}n.m=e,n.c=t,n.d=function(e,t,o){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:o})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)n.d(o,r,function(t){return e[t]}.bind(null,r));return o},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="/",n(n.s=300)}({300:function(e,t,n){e.exports=n(301)},301:function(e,t){window.ilabStorageBrowser=function(e,t,n){var o=e("div.ilab-storage-browser"),r=e("div.ilab-storage-browser > div.mcsb-container").find("table"),i=o.find("div.ilab-storage-browser-header"),a=!1,s=[],l=[],c=e("#mcsb-progress-modal"),d=c.find("div.mcsb-progress-label"),u=c.find("#mcsb-progress"),p=o.find(".button-upload"),f=o.find(".button-create-folder"),h=o.find(".button-import"),b=o.find(".mcsb-actions > .mcsb-buttons > .button-delete"),m=[],v=0,g=[],y=0,w=e("#mcsb-upload-modal"),k=e("div.mcsb-upload-items-container"),x=0,C=0,D=null,_=e("#mcsb-import-options-modal"),U=_.find(".button-cancel"),P=_.find(".button-import"),S=!1,j=function(t){var n={action:"ilab_browser_track",nonce:browserNonce,track:t};e.post(ajaxurl,n,(function(e){console.log(e)}))};if("undefined"!=typeof ilabMediaUploader&&"function"==typeof ilabMediaUploader&&t){var N=["image/jpg","image/jpeg","image/png"],O=!1,E=!1,T=!1,M=!1,B=1;"undefined"!=typeof mediaCloudDirectUploadSettings&&(N=mediaCloudDirectUploadSettings.allowedMimes,O=mediaCloudDirectUploadSettings.imgixEnabled,E=mediaCloudDirectUploadSettings.videoEnabled,T=mediaCloudDirectUploadSettings.docsEnabled,M=mediaCloudDirectUploadSettings.extrasEnabled,mediaCloudDirectUploadSettings.maxUploads>0&&(B=mediaCloudDirectUploadSettings.maxUploads)),(D=new ilabMediaUploader(e,{clickToUpload:!1,insertMode:!1,maxUploads:B,imgixEnabled:O,videoEnabled:E,docsEnabled:T,extrasEnabled:M,insertButton:e("#ilab-insert-button"),uploadTarget:e("#wpbody"),attachmentContainer:e("#ilab-attachment-info"),cellContainer:e("#mcsb-upload-items-container"),uploadItemTemplate:wp.template("ilab-upload-cell"),attachmentTemplate:wp.template("ilab-attachment-info"),allowedMimes:N,sendDirectory:!0})).setUploadDirectory(browserCurrentPath),console.log(D)}else console.log("no uploader");e(document).on("ilab.upload-started",(function(e){console.log("upload started"),x++,S||(j("upload"),S=!0),clearTimeout(C),1==x&&(k.empty(),w.removeClass("hidden"))})),e(document).on("ilab.upload-finished",(function(e){--x<=0&&(C=setTimeout((function(){Y(browserCurrentPath,!1,(function(){w.addClass("hidden")}))}),2e3))})),p.on("click",(function(e){return e.preventDefault(),null!=D&&D.openUpload(),!1})),f.on("click",(function(t){t.preventDefault();var n=prompt("New folder name");if(null!=n){var o={action:"ilab_browser_create_directory",key:browserCurrentPath,nonce:browserNonce,directory:n};e.post(ajaxurl,o,(function(e){"ok"==e.status&&R(e)}))}return!1})),b.on("click",(function(e){return e.preventDefault(),n&&s.length>0&&confirm("Are you sure you want to delete these items?")&&(v=0,m=[],s.forEach((function(e){var t=e.data("key");m.push(t)})),d.text(""),u.css({width:"0%"}),c.removeClass("hidden"),A()),!1})),h.on("click",(function(e){if(e.preventDefault(),0==s.length)return alert("Select an item or items to import."),!1;_.removeClass("hidden")})),U.on("click",(function(e){return e.preventDefault(),_.addClass("hidden"),!1})),P.on("click",(function(t){t.preventDefault(),j("import"),_.addClass("hidden");var n=[];s.forEach((function(e){n.push(e.data("key"))}));var o={action:"ilab_browser_file_list",key:browserCurrentPath,nonce:browserNonce,keys:n,skipThumbnails:e("#import-option-skip-thumbnails").prop("checked"),importOnly:e("#import-option-skip-download").prop("checked"),preservePaths:e("#import-option-preserve-paths").prop("checked")};return d.text("Building file list ..."),u.css({width:"0%"}),c.removeClass("hidden"),e.post(ajaxurl,o,(function(e){console.log(e),"ok"==e.status&&e.files.length>0&&(g=e.files,y=0,I())})),!1}));var I=function t(){var n=g[y];d.text("Importing "+(y+1)+" of "+g.length+" '"+n.key+"' ..."),u.css({width:y/g.length*100+"%"});var o={action:"ilab_browser_import_file",key:n.key,thumbs:n.thumbs,nonce:browserNonce,skipThumbnails:e("#import-option-skip-thumbnails").prop("checked"),importOnly:e("#import-option-skip-download").prop("checked"),preservePaths:e("#import-option-preserve-paths").prop("checked")};e.post(ajaxurl,o,(function(e){console.log(e),e.hasOwnProperty("nextNonce")&&(browserNonce=e.nextNonce),"error"==e.status?(u.css({width:"100%"}),c.addClass("hidden"),setTimeout((function(){alert("There was an error attempting to import items.\n\nIf you are using Google Cloud Storage, this is typically a permissions error.  You must set the service account you are using as the owner of the objects.")}),500)):++y>=g.length?(u.css({width:"100%"}),c.addClass("hidden")):t()}))},A=function t(){if(n){var o=m[v];d.text("Deleting "+(v+1)+" of "+m.length+" '"+o+"' ..."),u.css({width:v/m.length*100+"%"});var r={action:"ilab_browser_delete",key:browserCurrentPath,nonce:browserNonce,keys:[o]};e.post(ajaxurl,r,(function(e){"ok"==e.status?R(e):e.hasOwnProperty("nextNonce")&&(browserNonce=e.nextNonce),++v>=m.length?(u.css({width:"100%"}),c.addClass("hidden")):t()}))}},L=function(){0==s.length?(h.addClass("disabled"),b.addClass("disabled")):(h.removeClass("disabled"),b.removeClass("disabled"))},G=function(){l=[],s=[],r.find("tr[data-key]").each((function(){var t=e(this),n=null;l.push(t),t.find("td > input[type=checkbox]").each((function(){n=this,e(this).on("change",(function(){if(this.checked)s.push(t);else{var e=s.indexOf(t);e>-1&&s.splice(e,1)}L()}))})),"dir"==e(this).data("file-type")?e(this).on("click",(function(n){if(!e(n.target).is("input"))return n.preventDefault(),t.find("img.loader").css({display:"block"}),t.find("td > span").css({display:"none"}),Y(e(this).data("key"),!0),!1})):(t.find(".ilab-browser-action-delete").on("click",(function(e){return e.preventDefault(),confirm("Are you sure you want to delete this item?")&&(v=0,m=[t.data("key")],d.text(""),u.css({width:"0%"}),c.removeClass("hidden"),A()),!1})),e(this).on("click",(function(o){if(!e(o.target).is("input")&&!e(o.target).is("a")){if(o.preventDefault(),n.checked=!n.checked,n.checked)s.push(t);else{var r=s.indexOf(t);r>-1&&s.splice(r,1)}return L(),!1}})))})),r.find("th > input[type=checkbox]").on("change",(function(){s=[];var e=this;l.forEach((function(t){t.find("td > input[type=checkbox]").each((function(){this.checked=e.checked,e.checked&&s.push(t)})),L()}))})),L()},R=function(t){var n=e(t.table),o=e(t.header);o.insertBefore(i),n.insertBefore(r),i.remove(),r.remove(),r=n,i=o,G(),q(),t.hasOwnProperty("nextNonce")&&(browserNonce=t.nextNonce)},Y=function(t,n){var o=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null;a=!0;var r={action:"ilab_browser_select_directory",nonce:browserNonce,key:t};e.post(ajaxurl,r,(function(e){if("ok"==e.status){if(R(e),n){browserCurrentPath=t,null!=D&&D.setUploadDirectory(t);var r={path:browserCurrentPath};history.pushState(r,"",browserBaseURL+"&path="+t)}null!=o&&o()}}))},q=function(){o.find('a[data-file-type="dir"]').on("click",(function(t){return t.preventDefault(),Y(e(this).data("key"),!0),!1}))};G(),q(),window.addEventListener("popstate",(function(e){if(a){var t=null!=e.state&&e.state.hasOwnProperty("path")?e.state.path:"";return e.preventDefault(),Y(t,!1),!1}}))}}});