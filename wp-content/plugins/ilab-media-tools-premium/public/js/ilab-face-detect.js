!function(t){var e={};function r(n){if(e[n])return e[n].exports;var i=e[n]={i:n,l:!1,exports:{}};return t[n].call(i.exports,i,i.exports,r),i.l=!0,i.exports}r.m=t,r.c=e,r.d=function(t,e,n){r.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:n})},r.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},r.t=function(t,e){if(1&e&&(t=r(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(r.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var i in t)r.d(n,i,function(e){return t[e]}.bind(null,i));return n},r.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return r.d(e,"a",e),e},r.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},r.p="/",r(r.s=609)}({108:function(t,e,r){var n=function(t){"use strict";var e,r=Object.prototype,n=r.hasOwnProperty,i="function"==typeof Symbol?Symbol:{},o=i.iterator||"@@iterator",a=i.asyncIterator||"@@asyncIterator",h=i.toStringTag||"@@toStringTag";function u(t,e,r,n){var i=e&&e.prototype instanceof p?e:p,o=Object.create(i.prototype),a=new Y(n||[]);return o._invoke=function(t,e,r){var n=d;return function(i,o){if(n===l)throw new Error("Generator is already running");if(n===s){if("throw"===i)throw o;return _()}for(r.method=i,r.arg=o;;){var a=r.delegate;if(a){var h=M(a,r);if(h){if(h===y)continue;return h}}if("next"===r.method)r.sent=r._sent=r.arg;else if("throw"===r.method){if(n===d)throw n=s,r.arg;r.dispatchException(r.arg)}else"return"===r.method&&r.abrupt("return",r.arg);n=l;var u=c(t,e,r);if("normal"===u.type){if(n=r.done?s:f,u.arg===y)continue;return{value:u.arg,done:r.done}}"throw"===u.type&&(n=s,r.method="throw",r.arg=u.arg)}}}(t,r,a),o}function c(t,e,r){try{return{type:"normal",arg:t.call(e,r)}}catch(t){return{type:"throw",arg:t}}}t.wrap=u;var d="suspendedStart",f="suspendedYield",l="executing",s="completed",y={};function p(){}function m(){}function g(){}var x={};x[o]=function(){return this};var w=Object.getPrototypeOf,v=w&&w(w(R([])));v&&v!==r&&n.call(v,o)&&(x=v);var E=g.prototype=p.prototype=Object.create(x);function b(t){["next","throw","return"].forEach(function(e){t[e]=function(t){return this._invoke(e,t)}})}function L(t){var e;this._invoke=function(r,i){function o(){return new Promise(function(e,o){!function e(r,i,o,a){var h=c(t[r],t,i);if("throw"!==h.type){var u=h.arg,d=u.value;return d&&"object"==typeof d&&n.call(d,"__await")?Promise.resolve(d.__await).then(function(t){e("next",t,o,a)},function(t){e("throw",t,o,a)}):Promise.resolve(d).then(function(t){u.value=t,o(u)},function(t){return e("throw",t,o,a)})}a(h.arg)}(r,i,e,o)})}return e=e?e.then(o,o):o()}}function M(t,r){var n=t.iterator[r.method];if(n===e){if(r.delegate=null,"throw"===r.method){if(t.iterator.return&&(r.method="return",r.arg=e,M(t,r),"throw"===r.method))return y;r.method="throw",r.arg=new TypeError("The iterator does not provide a 'throw' method")}return y}var i=c(n,t.iterator,r.arg);if("throw"===i.type)return r.method="throw",r.arg=i.arg,r.delegate=null,y;var o=i.arg;return o?o.done?(r[t.resultName]=o.value,r.next=t.nextLoc,"return"!==r.method&&(r.method="next",r.arg=e),r.delegate=null,y):o:(r.method="throw",r.arg=new TypeError("iterator result is not an object"),r.delegate=null,y)}function X(t){var e={tryLoc:t[0]};1 in t&&(e.catchLoc=t[1]),2 in t&&(e.finallyLoc=t[2],e.afterLoc=t[3]),this.tryEntries.push(e)}function T(t){var e=t.completion||{};e.type="normal",delete e.arg,t.completion=e}function Y(t){this.tryEntries=[{tryLoc:"root"}],t.forEach(X,this),this.reset(!0)}function R(t){if(t){var r=t[o];if(r)return r.call(t);if("function"==typeof t.next)return t;if(!isNaN(t.length)){var i=-1,a=function r(){for(;++i<t.length;)if(n.call(t,i))return r.value=t[i],r.done=!1,r;return r.value=e,r.done=!0,r};return a.next=a}}return{next:_}}function _(){return{value:e,done:!0}}return m.prototype=E.constructor=g,g.constructor=m,g[h]=m.displayName="GeneratorFunction",t.isGeneratorFunction=function(t){var e="function"==typeof t&&t.constructor;return!!e&&(e===m||"GeneratorFunction"===(e.displayName||e.name))},t.mark=function(t){return Object.setPrototypeOf?Object.setPrototypeOf(t,g):(t.__proto__=g,h in t||(t[h]="GeneratorFunction")),t.prototype=Object.create(E),t},t.awrap=function(t){return{__await:t}},b(L.prototype),L.prototype[a]=function(){return this},t.AsyncIterator=L,t.async=function(e,r,n,i){var o=new L(u(e,r,n,i));return t.isGeneratorFunction(r)?o:o.next().then(function(t){return t.done?t.value:o.next()})},b(E),E[h]="Generator",E[o]=function(){return this},E.toString=function(){return"[object Generator]"},t.keys=function(t){var e=[];for(var r in t)e.push(r);return e.reverse(),function r(){for(;e.length;){var n=e.pop();if(n in t)return r.value=n,r.done=!1,r}return r.done=!0,r}},t.values=R,Y.prototype={constructor:Y,reset:function(t){if(this.prev=0,this.next=0,this.sent=this._sent=e,this.done=!1,this.delegate=null,this.method="next",this.arg=e,this.tryEntries.forEach(T),!t)for(var r in this)"t"===r.charAt(0)&&n.call(this,r)&&!isNaN(+r.slice(1))&&(this[r]=e)},stop:function(){this.done=!0;var t=this.tryEntries[0].completion;if("throw"===t.type)throw t.arg;return this.rval},dispatchException:function(t){if(this.done)throw t;var r=this;function i(n,i){return h.type="throw",h.arg=t,r.next=n,i&&(r.method="next",r.arg=e),!!i}for(var o=this.tryEntries.length-1;o>=0;--o){var a=this.tryEntries[o],h=a.completion;if("root"===a.tryLoc)return i("end");if(a.tryLoc<=this.prev){var u=n.call(a,"catchLoc"),c=n.call(a,"finallyLoc");if(u&&c){if(this.prev<a.catchLoc)return i(a.catchLoc,!0);if(this.prev<a.finallyLoc)return i(a.finallyLoc)}else if(u){if(this.prev<a.catchLoc)return i(a.catchLoc,!0)}else{if(!c)throw new Error("try statement without catch or finally");if(this.prev<a.finallyLoc)return i(a.finallyLoc)}}}},abrupt:function(t,e){for(var r=this.tryEntries.length-1;r>=0;--r){var i=this.tryEntries[r];if(i.tryLoc<=this.prev&&n.call(i,"finallyLoc")&&this.prev<i.finallyLoc){var o=i;break}}o&&("break"===t||"continue"===t)&&o.tryLoc<=e&&e<=o.finallyLoc&&(o=null);var a=o?o.completion:{};return a.type=t,a.arg=e,o?(this.method="next",this.next=o.finallyLoc,y):this.complete(a)},complete:function(t,e){if("throw"===t.type)throw t.arg;return"break"===t.type||"continue"===t.type?this.next=t.arg:"return"===t.type?(this.rval=this.arg=t.arg,this.method="return",this.next="end"):"normal"===t.type&&e&&(this.next=e),y},finish:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var r=this.tryEntries[e];if(r.finallyLoc===t)return this.complete(r.completion,r.afterLoc),T(r),y}},catch:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var r=this.tryEntries[e];if(r.tryLoc===t){var n=r.completion;if("throw"===n.type){var i=n.arg;T(r)}return i}}throw new Error("illegal catch attempt")},delegateYield:function(t,r,n){return this.delegate={iterator:R(t),resultName:r,nextLoc:n},"next"===this.method&&(this.arg=e),y}},t}(t.exports);try{regeneratorRuntime=n}catch(t){Function("r","regeneratorRuntime = r")(n)}},52:function(t,e,r){t.exports=r(108)},609:function(t,e,r){t.exports=r(610)},610:function(t,e,r){"use strict";r.r(e);var n=r(52),i=r.n(n);function o(t,e,r,n,i,o,a){try{var h=t[o](a),u=h.value}catch(t){return void r(t)}h.done?e(u):Promise.resolve(u).then(n,i)}function a(t){return function(){var e=this,r=arguments;return new Promise(function(n,i){var a=t.apply(e,r);function h(t){o(a,n,i,h,u,"next",t)}function u(t){o(a,n,i,h,u,"throw",t)}h(void 0)})}}window.ILABFaceDetector=function(t,e){if("undefined"==typeof faceapi)return null;var r=function(t,e){var r=Number.MAX_SAFE_INTEGER,n=Number.MAX_SAFE_INTEGER,i=0,o=0;return t.forEach(function(t){r=Math.min(r,t.x),i=Math.max(i,t.x),n=Math.min(n,t.y),o=Math.max(o,t.y)}),{x1:r/e.width,x2:i/e.width,y1:n/e.height,y2:o/e.height,midX:(r+(i-r)/2)/e.width,midY:(n+(o-n)/2)/e.height,width:(i-r)/e.width,height:(o-n)/e.height}},n=function(t,e,r){var n=[];return t.forEach(function(t){var i=Math.sin(e),o=Math.cos(e);n.push({x:o*(t.x-r.x)-i*(t.y-r.y)+r.x,y:i*(t.x-r.x)+o*(t.y-r.y)+r.y})}),n},o=function(t,e){return{x:t.x/e.width,y:t.y/e.height}},h=function(t,e,i){var a=n(t,-e.angle,e.center),h=r(a,{width:1,height:1}),u=[{x:h.x1,y:h.y1},{x:h.x2,y:h.y2},{x:h.x1,y:h.y2},{x:h.x2,y:h.y1},{x:h.midX,y:h.y1},{x:h.x2,y:h.midY},{x:h.midX,y:h.y2},{x:h.x1,y:h.midY},{x:h.midX,y:h.midY}];return{x1:(u=n(u,e.angle,e.center))[0].x/i.width,x2:u[1].x/i.width,y1:u[0].y/i.height,y2:u[1].y/i.height,midX:u[2].x/i.width,midY:u[2].y/i.height,width:h.width/i.width,height:h.height/i.height,topLeft:o(u[0],i),bottomRight:o(u[1],i),bottomLeft:o(u[2],i),topRight:o(u[3],i),topMiddle:o(u[4],i),middleRight:o(u[5],i),bottomMiddle:o(u[6],i),middleLeft:o(u[7],i)}},u=function(t){var e=r(t.getLeftEye(),{width:1,height:1}),n=r(t.getRightEye(),{width:1,height:1}),i=Math.max(n.midX,e.midX),o=Math.min(n.midX,e.midX),a=Math.max(n.midY,e.midY),h=o-i,u=Math.min(n.midY,e.midY)-a;return{angle:Math.atan2(n.midY-e.midY,n.midX-e.midX),center:{x:i+h/2,y:a+u/2}}},c=function(){var t=a(i.a.mark(function t(){return i.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return t.abrupt("return",new Promise(function(t){setTimeout(function(){t()},250)}));case 1:case"end":return t.stop()}},t)}));return function(){return t.apply(this,arguments)}}(),d=function(){var t=a(i.a.mark(function t(o){var a;return i.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:if(window.loadedFaceData){t.next=6;break}return console.log("waiting for loaded."),t.next=4,c();case 4:t.next=0;break;case 6:return a=[],t.next=9,faceapi.detectAllFaces(o).withFaceLandmarks();case 9:t.sent.forEach(function(t){var e=t.detection.imageDims,i={Left:t.detection.box.x/e.width,Top:t.detection.box.y/e.height,Width:t.detection.box.width/e.width,Height:t.detection.box.height/e.height},o=[],c=u(t.landmarks);c.center={x:i.Left+i.Width/2,y:i.Top+i.Height/2};var d=h(t.landmarks.getMouth(),c,t.detection.imageDims);0!=d.width&&0!=d.height&&(o.push({Type:"mouthLeft",X:d.middleLeft.x,Y:d.middleLeft.y}),o.push({Type:"mouthRight",X:d.middleRight.x,Y:d.middleRight.y}),o.push({Type:"mouthUp",X:d.topMiddle.x,Y:d.topMiddle.y}),o.push({Type:"mouthDown",X:d.bottomMiddle.x,Y:d.bottomMiddle.y}));var f=h(t.landmarks.getLeftEye(),c,t.detection.imageDims);0!=f.width&&0!=f.height&&(o.push({Type:"eyeLeft",X:f.midX,Y:f.midY}),o.push({Type:"leftEyeLeft",X:f.middleLeft.x,Y:f.middleLeft.y}),o.push({Type:"leftEyeRight",X:f.middleRight.x,Y:f.middleRight.y}),o.push({Type:"leftEyeUp",X:f.topMiddle.x,Y:f.topMiddle.y}),o.push({Type:"leftEyeDown",X:f.bottomMiddle.x,Y:f.bottomMiddle.y}));var l=h(t.landmarks.getLeftEyeBrow(),c,t.detection.imageDims);0!=l.width&&0!=l.height&&(o.push({Type:"leftEyeBrowLeft",X:l.bottomLeft.x,Y:l.bottomLeft.y}),o.push({Type:"leftEyeBrowRight",X:l.bottomRight.x,Y:l.bottomRight.y}),o.push({Type:"leftEyeBrowUp",X:l.topMiddle.x,Y:l.topMiddle.y}));var s=h(t.landmarks.getRightEye(),c,t.detection.imageDims);0!=s.width&&0!=s.height&&(o.push({Type:"eyeRight",X:s.midX,Y:s.midY}),o.push({Type:"rightEyeLeft",X:s.middleLeft.x,Y:s.middleLeft.y}),o.push({Type:"rightEyeRight",X:s.middleRight.x,Y:s.middleRight.y}),o.push({Type:"rightEyeUp",X:s.topMiddle.x,Y:s.topMiddle.y}),o.push({Type:"rightEyeDown",X:s.bottomMiddle.x,Y:s.bottomMiddle.y}));var y=h(t.landmarks.getRightEyeBrow(),c,t.detection.imageDims);0!=y.width&&0!=y.height&&(o.push({Type:"rightEyeBrowLeft",X:y.bottomLeft.x,Y:y.bottomLeft.y}),o.push({Type:"rightEyeBrowRight",X:y.bottomRight.x,Y:y.bottomRight.y}),o.push({Type:"rightEyeBrowUp",X:y.topMiddle.x,Y:y.topMiddle.y}));var p=h(t.landmarks.getNose(),c,t.detection.imageDims);0!=p.width&&0!=p.height&&(o.push({Type:"nose",X:p.midX,Y:p.midY}),o.push({Type:"noseLeft",X:p.bottomLeft.x,Y:p.bottomLeft.y}),o.push({Type:"noseRight",X:p.bottomRight.x,Y:p.bottomRight.y}),o.push({Type:"noseUp",X:p.topMiddle.x,Y:p.topMiddle.y}),o.push({Type:"noseDown",X:p.bottomMiddle.x,Y:p.bottomMiddle.y}));var m=t.landmarks.getJawOutline();if(m.length>0){var g=n(m,-c.angle,c.center),x=r(g,{width:1,height:1}),w={x:Number.MAX_SAFE_INTEGER,y:Number.MAX_SAFE_INTEGER},v={x:0,y:Number.MAX_SAFE_INTEGER},E={x:Number.MAX_SAFE_INTEGER,y:Number.MAX_SAFE_INTEGER},b={x:0,y:Number.MAX_SAFE_INTEGER},L={x:x.midX,y:x.y2};g.forEach(function(t){t.y<x.midY?t.x<x.midX?(w.x=Math.min(w.x,t.x),w.y=Math.min(w.y,t.y)):(v.x=Math.max(v.x,t.x),v.y=Math.min(v.y,t.y)):t.x<x.midX?(E.x=Math.min(E.x,t.x),E.y=Math.min(E.y,t.y)):(b.x=Math.max(b.x,t.x),b.y=Math.min(b.y,t.y))});var M=n([w,v,E,b,L],c.angle,c.center);o.push({Type:"upperJawlineLeft",X:M[0].x/e.width,Y:M[0].y/e.height}),o.push({Type:"upperJawlineRight",X:M[1].x/e.width,Y:M[1].y/e.height}),o.push({Type:"midJawlineLeft",X:M[2].x/e.width,Y:M[2].y/e.height}),o.push({Type:"midJawlineRight",X:M[3].x/e.width,Y:M[3].y/e.height}),o.push({Type:"chinBottom",X:M[4].x/e.width,Y:M[4].y/e.height})}a.push({ImageDims:{width:t.detection.imageDims.width,height:t.detection.imageDims.height},BoundingBox:i,Landmarks:o})}),e(a);case 12:case"end":return t.stop()}},t)}));return function(e){return t.apply(this,arguments)}}();if(void 0!==t.tagName&&"IMG"==t.tagName)d(t);else{var f=new FileReader;f.onload=function(){var t=document.createElement("img");t.onload=function(){d(t)},t.src=f.result},f.readAsDataURL(t)}},window.loadingFaceData=!1,window.loadedFaceData=!1,"undefined"==typeof faceapi||window.loadingFaceData||(window.loadingFaceData=!0,a(i.a.mark(function t(){var e;return i.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return 0===(e=LocalVisionMediaURL).indexOf("//")&&(e=document.location.protocol+e),console.log("loading data from ..."+e),t.next=5,faceapi.loadSsdMobilenetv1Model(e);case 5:return t.next=7,faceapi.loadFaceLandmarkModel(e);case 7:console.log("finished loading data ..."),window.loadedFaceData=!0;case 9:case"end":return t.stop()}},t)}))())}});