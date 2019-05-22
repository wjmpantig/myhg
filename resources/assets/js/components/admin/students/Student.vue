<template>
	<div>
		<h2 class="title">{{user.last_name}} {{user.first_name}}</h2>

		<div class="field">
			<label for="" class="label">Last name</label>
			<div class="control">
				<input type="text"  :class="{'input':true,'is-danger':hasError('last_name')}" v-model="last_name" @blur="updateField('last_name')">
			</div>
			<p class="help is-danger" v-show="hasError('last_name')">{{getError('last_name')}}</p>
		</div>
		<div class="field">
			<label for="" class="label">First name</label>
			<div class="control">
				<input type="text"  :class="{'input':true,'is-danger':hasError('first_name')}" v-model="first_name" @blur="updateField('first_name')">
			</div>
			<p class="help is-danger" v-show="hasError('first_name')">{{getError('first_name')}}</p>
		</div>
		<!-- <div class="field">
			<label for="" class="label">Email</label>
			<div class="control">
				<input type="text" :class="{'input':true,'is-danger':hasError('email')}" v-model="email" @blur="updateField('email')">
			</div>
			<p class="help is-danger" v-show="hasError('email')">{{getError('email')}}</p>
		</div> -->

		<table class="table is-bordered is-striped is-hoverable">
			<thead>
				<tr>
					<th>Sections</th>			
				</tr>
			</thead>
			<tbody>
				<tr v-for="(section,index) in user.sections">
					<td>
						<router-link :to="{path:'/sections/' + section.id}">{{section.name}}</router-link>
					
					</td>
					
				</tr>
			</tbody>
		</table>
	</div>
</template>
<script>
	export default{
		data(){
			return {
				user:{},
				first_name:null,
				last_name:null,
				email:null,
				errors:{}
			};
		},
		mounted(){
			axios.get('/api/students/'+this.$route.params.id).then(response=>{
				let user = this.user = response.data;
				this.first_name= user.first_name;
				this.last_name = user.last_name;
				this.email = user.email;
			});
		},
		methods:{
			hasError(field){
				let error= this.errors;
				if(error.errors &&  error.errors[field]){
					return true;
				}
				return false;
			},
			getError(field){
				let error= this.errors;
				if(error.errors && error.errors[field]){
					if(Array.isArray(error.errors[field]) && error.errors[field].length == 1){
						return error.errors[field][0];
					}
					return error.errors[field];
				}
				return null;
			},
			updateField(field){
				let data = {};
				if(field == 'first_name'){
					data.first_name = this.first_name;
				}else if(field=='last_name'){
					data.last_name = this.last_name;
				}else if(field=='email'){
					data.email = this.email;
				}else{
					return;
				}
				axios.post('/api/students/'+this.user.id,data).then(response=>{
					this.user = response.data;
					this[field] = response.data[field];
				}).catch(err=>{
					console.error(err)
					this.errors = err.data;
				});
			}
		}

	}
</script>