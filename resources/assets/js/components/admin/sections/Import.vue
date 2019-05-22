<template>
   <div>
      <h1 class="is-title">Import students</h1>
      <p>Import a CSV file with "first_name" and "last_name" as headers</p>
      <form @submit.prevent="submit()" enctype="multipart/form-data" :disabled="loading">
         <input type="file" ref="file" @change="fileChange()" />
         <button class="button" type="submit">Upload</button>
         <font-awesome-icon :icon="['fas','spinner']" class="fa-spin" v-show="loading"></font-awesome-icon>

      </form>
         <p class="help is-danger" v-show="!!errorMessage">
            {{errorMessage}}
         </p>
      <div v-for="error,key in errors">
         <p class="help is-danger" v-for="message in error">
            {{message}}
         </p>
      </div>
      <div v-show="total !== null && rows !== null">
         <p class="help is-info">
            Total rows read: {{rows}} <br/>
            Total students added: {{total}}
         </p>
      </div>
   </div>
</template>
<script>
   export default{
      data(){
         return {
            file: null,
            loading: false,
            total: null,
            rows: null,
            uploadSuccess: false,
            errors: [],
            errorMessage: null,
         }
      },
      methods:{
         fileChange(){
            this.file = this.$refs.file.files[0];
         },
         submit(){
            if(this.loading){
               return;
            }
            this.loading = true;
            this.total = null;
            this.rows = null;
            this.errors = [];
            this.errorMessage = null;
            const formData = new FormData();
            formData.append('file', this.file);
            axios.post('/api/sections/' + this.$route.params.id+'/import',formData,{
               headers: {
                  'Content-Type': 'multipart/form-data'
               }
            }).then(res=>{
               const { rows, total } = res.data;
               this.rows = rows;
               this.total = total;
               this.loading = false;
            }).catch(err=>{
               console.log(err);
               this.errorMessage = err.data.message;
               this.errors = err.data.errors;
               // this.errors = ['Upload error, file must be a valid CSV file'];
               this.loading = false;
            })
         }
      }
   }
</script>