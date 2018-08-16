<template>	
	<div>
		<h2 class="title">New Student</h2>
		<form @submit.prevent="submit()">
			<div class="field">
				<label for="" class="label">Last name</label>
				<div class="control">
					<input type="text"  :class="{'input':true,'is-danger':hasError('last_name')}" v-model="last_name" >
				</div>
				<p class="help is-danger" v-show="hasError('last_name')">{{getError('last_name')}}</p>
			</div>
			<div class="field">
				<label for="" class="label">First name</label>
				<div class="control">
					<input type="text"  :class="{'input':true,'is-danger':hasError('first_name')}" v-model="first_name">
				</div>
				<p class="help is-danger" v-show="hasError('first_name')">{{getError('first_name')}}</p>
			</div>
			<div class="field">
				<label for="season" class="label">Season</label>
				<div class="control">
					<div class="select" name="season">
						<select v-model="season" @change="loadSections()">
							<option v-for="season in seasons" :value="season.id">{{season.name}}</option>
						</select>
					</div>
					
				</div>
			</div>
			<div class="field" v-show="sections.length > 0">
				<label for="section" class="label">Section</label>
				<div class="control">
					<div class="select" name="section">
						<select v-model="section">
							<option v-for="section in sections" :value="section.id">{{section.name}}</option>
						</select>
					</div>
					
				</div>
			</div>
			<button class="button is-primary" type="submit">Submit</button>
		</form>
	</div>
</template>
<script>
	export default{
		data(){
			return {
				first_name:null,
				last_name:null,
				section:{},
				errors:{},
				seasons:[],
				sections:[],
				season:{},

			}
		},
		mounted(){
			axios.get('/api/seasons').then(response=>{
				this.seasons = response.data;
				this.season = this.seasons[0].id;
				this.loadSections();
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
			loadSections(){
				this.sections = [];
				axios.get('api/sections/',{
					params:{
						season_id: this.season
					}
				}).then(response=>{
					this.sections = response.data;
				}).catch(err=>{
					console.error(err);
				});
			},
			submit(){
				console.log('submit')
			}
		}
	}
</script>