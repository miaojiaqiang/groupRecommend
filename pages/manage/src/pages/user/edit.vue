<template>
    <div>
        <el-form ref="form" :rules="rules" :model="post" label-width="80px" v-loading="pageLoading">
            <el-form-item label="昵称" prop="name">
                <el-input v-model="post.name"></el-input>
            </el-form-item>
            <el-form-item label="用户身份" prop="type">
                <el-select v-model="post.type" placeholder="请选择">
                    <el-option v-for="(type, key) in typeText" :key="key" :label="type" :value="parseInt(key)"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="用户地址" prop="address" v-if="post.address && mapKey && mapUrl">
                <el-input v-model="post.address" disabled=""></el-input>
                <iframe allow="geolocation" width="100%" height="600" frameborder="0" :src="mapUrl">
                </iframe>
            </el-form-item>
            <el-form-item label="所在群聊" prop="phone">
                <el-select v-model="post.group" multiple filterable remote reserve-keyword placeholder="请输入关键词"
                           :remote-method="searchGroup"
                           :loading="searchGroupLoading">
                    <el-option v-for="group in groupLists" :key="group.id" :label="group.name" :value="group.id"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="活跃星级" prop="active">
                <el-rate v-model="post.active" style="margin-top: 10px;"></el-rate>
            </el-form-item>
            <el-form-item label="排序" prop="order">
                <el-input type="number" v-model="post.order"></el-input>
            </el-form-item>
            <el-form-item label="拉黑" prop="block">
                <el-switch v-model="post.block"></el-switch>
            </el-form-item>
        </el-form>
        <div slot="footer" class="center">
            <el-button type="primary" @click="onSubmit" :loading="buttonLoading">确定</el-button>
        </div>
    </div>
</template>
<script>

    export default {
        props: {
            id: Number
        },
        data() {
            return {
                typeText: {
                    1: '用户',
                    2: '商家',
                },
                post: {},
                rules: {
                    name: [
                        {required: true, message: '请输入用户名', trigger: 'blur'},
                        {min: 2, max: 15, message: '长度在 2 到 15 个字符', trigger: 'blur'}
                    ],
                },
                buttonLoading: false,
                pageLoading: false,
                searchGroupLoading: false,
                groupLists: [],
                mapKey: null,
                address: {}
            }
        },
        computed: {
            mapUrl() {
                let url = "https://apis.map.qq.com/tools/locpicker?search=1&type=1&key=" + this.mapKey + "&referer=myapp"
                if (this.address.longitude && this.address.latitude)
                    url += '&coord=' + this.address.longitude + ',' + this.address.latitude
                return url
            }
        },
        watch: {
            id() {
                this.doLoad()
            }
        },
        mounted() {
            this.doLoad()
        },
        methods: {
            async doLoad() {
                await this.doResetForm()
                this.getInfo()
                this.getMap()
            },
            getInfo() {
                this.pageLoading = true
                this.$request.get(this.$url.user.member + '/' + this.id).then(res => {
                    this.pageLoading = false
                    this.post = res.data.data
                    this.groupLists = this.post.group_id
                    this.address = {
                        latitude: res.data.data.latitude,
                        longitude: res.data.data.longitude
                    }
                }).catch(error => {
                    this.pageLoading = false
                });
            },
            onSubmit() {
                this.doValidate(() => {
                    this.buttonLoading = true
                    this.$request.put(this.$url.user.member + '/' + this.id, this.post).then(res => {
                        this.buttonLoading = false
                        this.$notify({title: '编辑成功', type: 'success'});

                        this.$emit('success')
                        this.$emit('close')
                    }).catch(error => {
                        this.buttonLoading = false
                    });
                })
            },
            getMapKey() {
                this.$request.get(this.$url.home.map_key).then(res => {
                    this.mapKey = res.data.data
                })
            },
            getMap() {
                this.getMapKey()
                window.removeEventListener('message', event => {
                }, false)
                window.addEventListener('message', event => {
                    var loc = event.data;
                    if (loc && loc.module == 'locationPicker') {
                        this.post.longitude = loc.latlng.lng
                        this.post.latitude = loc.latlng.lat
                        this.post.address = loc.poiaddress
                    }
                }, false);
            },
            searchGroup(query) {
                this.searchGroupLoading = true;
                this.$request.get(this.$url.home.group_search, {params: {keyword: query}}).then(res => {
                    this.searchGroupLoading = false
                    this.groupLists = res.data.data
                }).catch(error => {
                    this.searchGroupLoading = false
                });
            },
            doValidate(callback) {
                this.$refs['form'].validate((valid) => {
                    if (valid)
                        return callback()
                    return false;
                });
            },
            async doResetForm() {
                await this.$refs['form'].resetFields();
            }
        }
    }
</script>