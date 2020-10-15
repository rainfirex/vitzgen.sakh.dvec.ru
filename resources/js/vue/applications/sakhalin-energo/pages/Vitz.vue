<template>
    <div class="container-fluid bg p-4">
        <h5 class="text-center">Замена лицевых счетов <b>Сбербанк и Почта</b> на лицевые счета <b>energo</b></h5>
        <div class="se-group col-md-8 offset-md-2 mb-2">
            <p class="text-center">Необходимо загрузить:</p>
                <ul class="pl-4 pr-4">
                    <li>- Файл "abonents.txt" - справочник лицевых счетов</li>
                    <li>- Файл формат SB[числа] или файл формат E[числа]</li>
                </ul>

            <form action="/api/upload" method="POST" @submit.prevent="uploadFile">
                <div class="col-md-12 mb-4">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input"
                               id="inputGroupFile"
                               accept="text/plain,.csv,application/vnd.ms-excel"
                               multiple @change="selectFiles"
                               lang="ru">
                        <label class="custom-file-label" for="inputGroupFile">Выберите файлы</label>
                    </div>

                    <div class="form-group text-center pt-2">
                        <input type="submit" class="btn btn-dark"  value="Загрузить" :disabled="!isUpload">
                    </div>

                    <div class="mb-1 mt-3" v-if="files.length > 0">
                        <p class="text-center">Список загружаемых файлов</p>
                        <hr>
                        <ul class="list-group se-list">
                            <li v-for="(file, index) in files" class="list-group-item" v-bind:class="{upload: file.isUpload}"><b>{{index+1}}</b> - {{file.name}}</li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>



        <div v-if="isCompleted" @dblclick="isCompleted = !isCompleted" class="col-md-8 ml-auto mr-auto">
            <p class="text-center">Операция завершена</p>

            <div v-if="success" class="mt-4 alert alert-success">
                <p><b><i>Успех</i></b></p>
                <hr>
                <div v-html="success"></div>
                <div v-html="htmlFormed"></div>
            </div>

            <div v-if="error" class="mt-4 alert alert-danger">
                <p><b><i>Ошибка</i></b></p>
                <hr>
                <div v-html="error"></div>
                <div v-html="htmlFormed"></div>
            </div>
        </div>

        <div class="col-md-8 ml-auto mr-auto ">
            <p class="text-center">Тип отчета</p>
            <select class="form-control" v-model="typeReport">
                <option value="sber">Сбербанк</option>
                <option value="mail">Почта</option>
            </select>
        </div>

        <!-- LIST-FILES-->
        <div class="col-md-8 ml-auto mr-auto mt-4 mb-4">
            <p class="text-center">Загруженные файлы</p>
<!--            multiple="multiple" size="14"-->
            <select class="form-control" v-model="currentFile" ref="listFiles">
                <option v-for="file in listFile" :value="file" selected>{{file}}</option>
            </select>
        </div>

        <!-- BTN-FORM-->
        <div class="form-group text-center">
            <button type="submit" class="btn btn-dark" :disabled="!isGenerate" @click="generate">Формировать</button>
        </div>

        <!-- FILE-INFO-->
        <div v-if="response">
            <p class="text-center">{{response.timerWork}}</p>
            <div class="mt-4 alert alert-primary" v-if="response.result">
                <p><b>{{response.message}}</b></p>
                <hr>
                <div v-if="response.countSubscribers > 0" title="Сопоставленные данные. Абоненты, лицевые счета которых обновлены из файл 'abonents.txt'">
                    <p>Файл сформирован <a :href="response.urlSubscribers" download>{{response.filenameSubscribers}}</a> Количество записей - (<span>{{response.countSubscribers}}</span>)</p>
                </div>
                <div v-if="response.countUnknowns > 0" title="Абоненты, которые остались без обновления лицевых счетов.">
                    <p>Файл сформирован <a :href="response.urlUnknowns" download>{{response.filenameUnknowns}}</a> Количество записей - (<span>{{response.countUnknowns}}</span>)</p>
                </div>
                <div v-if="response.countMails > 0" title="Абоненты, которые остались без обновления лицевых счетов.">
                    <p>Файл сформирован <a :href="response.urlMails" download>{{response.filenameMails}}</a> Количество записей - (<span>{{response.countMails}}</span>)</p>
                </div>
            </div>
            <div class="mt-4 alert alert-danger" v-else="response.result">
                {{response.message}}
            </div>
        </div>

    </div>
</template>

<script>
    export default {
        name: "Vitz",
        data() {
            return {
                currentFile: null,
                fileIndex: 0,
                files: [],
                listFile: [],
                dirListFile: null,
                isUpload: false,
                isCompleted: false,
                isGenerate: true,
                htmlFormed: null,
                success: '',
                error: '',
                response: null,
                typeReport: 'sber',
            }
        },
        methods: {
            selectFiles(e) {
                const files = e.target.files;
                if (files.length > 0) {
                    // this.files = files;
                    const f = Array.from(files);
                    this.files = f.map(item => {
                        item.isUpload = true;
                        return item;
                    });

                    this.isUpload = true;
                    this.isCompleted = false;
                    this.success = '';
                    this.error = '';
                }
            },
            uploadFile(e) {
                if (this.files.length > 0) {
                    const form = e.target;
                    this.fileIndex = 0;
                    this.success = '';
                    this.error = '';
                    this.isUpload = false;
                    this.sendFile(form, this.files[this.fileIndex]);
                } else {
                    this.error = 'Список файлов';
                }
            },
            sendFile(form, file) {
                const data = new FormData();
                const _token = document.head.querySelector('meta[name="csrf-token"]').content;

                data.append('file', file);
                data.append('_token', _token);

                try{
                    axios.post(form.action, data, {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                            'X-CSRF-TOKEN': _token
                        }
                    }).then(response => {
                        if(response.status === 200) {
                            this.isUpload = false;
                            this.fileIndex++;
                            const data = response.data;

                            if (data.result) {
                                const f = Array.from(this.files);
                                this.files = f.map(item => {
                                    if (data.filename === item.name)
                                        item.isUpload = false;
                                    return item;
                                });

                                this.success +=  `Файл "${data.filename}" загружен.<br>`;
                                console.log('%c%s','color:green;', `Файл "${data.filename}" загружен.`);
                            } else {
                                this.error += `Файл "${data.filename}" не загружен - <i>${data.message}!</i><br>`;
                                console.log('%c%s','color:red;', `Файл "${data.filename}" не загружен - ${data.message}.`);
                            }

                            // сл. загрузка файла
                            if (this.files.length > this.fileIndex) {
                                this.sendFile(form, this.files[this.fileIndex]);
                            } else {
                                this.isCompleted = true;
                                this.files = [];
                                this.getFiles();
                                console.log('%c%s','color:green;', 'Загрузка завершена!');
                            }
                        }
                    });
                } catch (e) {
                    this.isUpload = false;
                    console.log('%c%s','color:red;', 'Ошибка запроса к серверу!');
                }
            },
            generate() {
                if (this.currentFile === undefined) {
                    this.isCompleted = true;
                    this.error = 'Файл не выбран.';
                    return;
                }

                this.htmlFormed = null;
                this.isGenerate = false;
                this.response = null;
                this.error = '';

                const url = `/api/generate/${this.currentFile}/${this.typeReport}`;
                axios.get(url).then(response => {
                    if (response.status === 200) {
                        this.files = [];
                        this.response = response.data;

                        if (response.error) {
                            this.error = response.error;
                        }
                        console.log('%c%s','color:green;', `Ответ получен: ${response.status} || ${response.statusText}`);

                        this.removeFile();
                    }
                }).catch(er => {
                    const error = er.response.data;
                    const status = er.response.status;
                    const statusText = er.response.statusText;

                    this.error = `${statusText}, status: ${status}<br>Ошибка => ${error.exception}, строка => ${error.line}<br>Файл => ${error.file}<br> Сообщение => ${error.message}`;
                    console.log('%c%s','color:red;', `Файл "${error.file}" не загружен. ${statusText}.`);

                }).finally(()=> {
                    this.isCompleted = true;
                    this.isGenerate = true;
                })
            },
            getFiles() {
                const request = '/api/get-files';
                axios.get(request).then(response => {
                    if (response.status === 200) {
                        const data = response.data;
                        this.listFile = data.files;
                        this.dirListFile = data.dir;
                        this.currentFile = this.listFile[0];
                    }
                });
            },
            removeFile() {
                axios.delete('/api/remove-file/'+this.currentFile).then(response => {
                    if (response.status === 200) {
                        this.listFile.splice(this.$refs.listFiles.selectedIndex, 1);
                        this.currentFile = this.listFile[0];
                        this.isGenerate = true;
                        console.log('%c%s','color:green;', `Ответ получен: ${response.status} || ${response.statusText}`);
                    }
                });
            },
            generators() {
                if (this.currentFile === undefined) {
                    this.isCompleted = true;
                    this.error = 'Файл не выбран.';
                    return;
                }

            }
        },
        mounted() {
            this.getFiles();
        }
    }
</script>

<style scoped>
    .bg {
        background: #dedede;
        border-radius: 5px;
        border: solid 1px #e0e0e2;
    }
    .upload {
        background: #dbe08c;
    }
    .se-group {
        border: solid 1px #aeaeae;
        border-radius: 5px;
    }
    .se-list {
        max-height: 300px;
        overflow: auto;
    }
</style>
