<template>
    <div>
        <div>
            <page-info>
                <div slot="title" v-text="title"></div>
                <el-button type="primary" size="small" icon="el-icon-plus" plain @click="action.add.show = true">
                    添加群
                </el-button>
            </page-info>
            <search :search="search" :load="doLoad">
                <el-form-item label="关键字" label-width="250">
                    <el-input v-model="search.keyword" placeholder="群名称/ID"></el-input>
                </el-form-item>
            </search>
            <div class="break-2"></div>
            <el-card class="box-card">
                <template>
                    <el-table :data="lists" key="user" v-loading="loading">
                        <el-table-column prop="id" label="ID" width="100"></el-table-column>
                        <el-table-column prop="name" label="姓名"></el-table-column>
                        <el-table-column prop="logo" label="LOGO">
                            <template slot-scope="scope">
                                <img :src="scope.row.logo" alt="" class="user-avatar" width="50">
                            </template>
                        </el-table-column>
                        <el-table-column prop="desc" label="群聊描述"></el-table-column>
                        <el-table-column prop="address" label="群地址"></el-table-column>
                        <el-table-column label="操作">
                            <template slot-scope="scope">
                                <el-tooltip content="编辑">
                                    <el-button icon="el-icon-edit" size="mini" @click="action.edit.id = scope.row.id,action.edit.show = true"></el-button>
                                </el-tooltip>
                                <button-delete :url="$url.home.group + '/' + scope.row.id " @success="doLoad"></button-delete>
                            </template>
                        </el-table-column>
                    </el-table>
                </template>
                <div class="table-pages">
                    <pages :data="pages"></pages>
                </div>
            </el-card>
        </div>
        <el-dialog :title="action.edit.title" :visible.sync="action.edit.show">
            <edit :id="action.edit.id" @close="action.edit.show = false" @success="doLoad"></edit>
        </el-dialog>
        <el-dialog :title="action.add.title" :visible.sync="action.add.show">
            <add @close="action.add.show = false" @success="doLoad"></add>
        </el-dialog>
    </div>
</template>
<script>
    import Edit from './edit.vue'
    import Add from './add.vue'

    export default {
        components: {
            'edit': Edit,
            'add': Add,
        },
        data() {
            return {
                action: {
                    edit: {
                        title: '编辑',
                        show: false,
                        id: 0
                    },
                    add: {
                        title: '添加',
                        show: false,
                    }
                },
                title: '微信群列表',
                showDelete: false,
                search: {},
                pages: {},
                lists: [],
                loading: false,
                typeText: {
                    1: '用户',
                    2: '商家',
                }
            };
        },
        watch: {
            $route: 'doLoad',
        },
        mounted() {
            this.doLoad()
        },
        methods: {
            doLoad() {
                this.search = this.$util.copyObj(this.$route.query)
                this.getLists()
            },
            getLists() {
                this.loading = true, this.lists = []
                this.$request.get(this.$url.home.group, {params: this.search}).then(res => {
                    this.lists = res.data.data
                    this.pages = res.data.meta.pagination
                    this.loading = false
                }).catch(res => {
                    this.loading = false
                })
            },
            upBlock(val, id) {
                // this.$request.patch(url + '/' + id + '/block', {block: val}).then(res => {
                // })
            }
        }

    }
</script>

<style lang="scss" scoped>

</style>