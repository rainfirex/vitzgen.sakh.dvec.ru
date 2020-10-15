export const send = (method, url, params, readyHandler, progressHandler, timeoutHandler, timeOut) => {
    // let resultJson = [];
    const xhttp = getXMLHttpRequest();
    if(xhttp === 'undefined') {
        alert('XMLHttpRequest не найден!');
        return;
    }

    xhttp.timeout = timeOut || 69600; //1556000
    method = method.toUpperCase();

    if (typeof timeoutHandler !== "undefined" && typeof timeoutHandler === "function") {
        xhttp.addEventListener('timeout', e => {
            timeoutHandler(e.target);
        })
    }

    if (typeof progressHandler !== "undefined" && typeof progressHandler === "function") {
        xhttp.addEventListener('progress', function (e) {
            if (e.lengthComputable) {
                // высчитываем процент загруженного
                const percentComplete = Math.ceil(e.loaded / e.total * 100);
                progressHandler(percentComplete);
            } else {
                progressHandler('Невозможно вычислить состояние загрузки, так как размер неизвестен');
            }
        });
    }

    if (typeof readyHandler  !== "undefined" && typeof readyHandler === "function") {
        xhttp.addEventListener('readystatechange', function () {
            // DONE - Операция полностью завершена.
            if(xhttp.readyState === 4) {
                this.resultJson = JSONparse(xhttp.responseText);
                readyHandler(xhttp.responseText);
            }
            // LOADING - Загрузка; responseText содержит частичные данные.
            if (xhttp.readyState === 3) {

            }
            // HEADERS_RECEIVED - Метод send() был вызван, доступны заголовки (headers) и статус.
            if (xhttp.readyState === 2) {
                // const progressMax  = this.getResponseHeader('X-Progress-Max');
                // console.dir(progressMax);
            }
        });
    }

    let xMethodOverride = null;
    if (method === 'PUT') {
        xMethodOverride = 'PUT';
        method = 'POST';
    }

    if (method === 'DELETE') {
        xMethodOverride = 'DELETE';
        method = 'POST';
    }

    if(method  === 'POST') {
        xhttp.open(method, url, true);
        // Если форм дата то заголовок не указывем
        // xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=utf-8");
        // xhttp.setRequestHeader("Content-Type", "application/x-binary; charset=x-user-defined");
        // xhttp.setRequestHeader('Content-Type', 'multipart/form-data');

        if (xMethodOverride !== null) {
            xhttp.setRequestHeader('X-HTTP-Method-Override', xMethodOverride);
        }

        if(params instanceof FormData) {
            //('is FormData!');
            xhttp.send(params);
        } else if (params instanceof Object){
            //('is Object!');
            xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=utf-8");
            xhttp.send(ser(params));
        } else {
            xhttp.send(null);
        }
    }

    if(method === 'GET') {
        if (params) {
            xhttp.open(method, url+'?'+ser(params), true);
        } else {
            xhttp.open(method, url, true);
        }
        xhttp.send(null);
    }

    return function abortRequest() {
        xhttp.abort();
    }
};

export const JSONparse = (string) => {
    if (string === '')
        return 'Данных нет!';

    try {
        return JSON.parse(string);
    } catch (e) {
        return string;
    }
};

export const isJsonString = (str) => {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
};

const getXMLHttpRequest = () => {
    let newRequest = null;
    if (window.XMLHttpRequest) { // Mozilla, Safari, ...
        newRequest = new XMLHttpRequest();
        if (newRequest.overrideMimeType)
            newRequest.overrideMimeType('text/xml');
    } else if (window.ActiveXObject) { // IE
        try {
            newRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                newRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) { }
        }
    }
    return newRequest;
};

const ser = (obj) => {
    let s = [];
    if(obj.constructor === 'Array') {
        for (let i = 0; i < obj.length; i++) {
            s.push(obj[i].name + '=' + encodeURIComponent(obj[i].value));
        }
    } else {
        for (let v in obj) {
            s.push(v+'='+encodeURIComponent(obj[v]));
        }
    }
    return s.join('&');
};
