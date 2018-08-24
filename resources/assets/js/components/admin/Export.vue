<template>
	<div>
		<h2 class="title">Export</h2>
		<form @submit.prevent="submit()">
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
			<div class="field">
				<label for="section" class="label">Section</label>
				<div class="control">
					<div class="select" name="section">
						<select v-model="section" >
							<option v-for="section in sections" :value="section.id">{{section.name}}</option>
						</select>
					</div>
					
				</div>
			</div>
			<div class="columns is-multiline">
				<div class="column is-narrow">
					<div class="columns">
						<div class="column">
							<label class="label">Attendance</label>
						</div>
						<div class="column">
							<input class="input" type="text" placeholder="" v-model="criteria['attendance']">		
						</div>
					</div>
				</div>
				<div class="column is-narrow" v-for="type in score_types">
					<div class="columns">
						<div class="column">
							<label class="label">{{type.name}}</label>
						</div>
						<div class="column">
							<input class="input" type="text" placeholder="" v-model="criteria[slugify(type.name)]">		
						</div>
					</div>
				</div>
				<div class="column is-narrow">
					<div class="columns">
						<div class="column">
							<label class="label">Teacher's Grade</label>
						</div>
						<div class="column">
							<input class="input" type="text" placeholder="" v-model="criteria['teachers-grade']">		
						</div>
					</div>
				</div>
		
				
			</div>
			<div class="columns is-multiline ">
				<div class="column is-narrow">
					<div class="columns">
						
						<div class="column is-narrow">
							<label class="label">Mark as Passed if final exam Passed</label>
						</div>
						<div class="column is-narrow">
							<input class="" type="checkbox" placeholder="" v-model="pass_finals">	
						</div>
					</div>
					
					
				</div>
				<div class="column is-narrow" v-show="pass_finals">
					<div class="columns">
						
						<div class="column is-narrow">
							<label class="label">Final exam passing grade</label>
						</div>
						<div class="column is-narrow">
							<input class="input" type="text" placeholder="" v-model="pass_finals_grade" :disabled="!pass_finals">	
						</div>
					</div>
				</div>
			
			</div>
			<div class="columns ">
				
				<div class="column is-narrow">
					<label class="label">Passing Grade</label>
				
				</div>
				<div class="column is-narrow">
					<input class="input" type="text" placeholder="" v-model="passing_grade">		
				</div>
			
			</div>
			<div class="columns ">
				
				<div class="column is-narrow">
					<label class="label">Total</label>
				</div>
				<div class="column is-narrow">
					<input :class="{'input':true,'is-danger':total!=100}" type="text" placeholder="" v-model="total" readonly="readonly">		
				</div>
			
			</div>
			<button class="button is-primary" type="submit">Export</button>
			<a class="button is-info" :href="fileLink" v-show="file">Download</a>
		</form>
	</div>
</template>
<script>
	export default{
		data(){
			return {
				season: null,
				section:null,
				sections: [],
				seasons: [],
				score_types:[],
				criteria:{
					
				},
				pass_finals: true,
				pass_finals_grade: 60,
				passing_grade: 60,
				file: null,
			}
		},
		computed:{
			total(){
				let total = 0;
				Object.values(this.criteria).forEach(val=>{
					total+=Number(val);
				})
				
				
				return total;
			},
			fileLink(){
				return this.file == null ? '/#' : '/export/' +this.file.id;
			}
		},
		mounted(){
			let criteria = {};
			let slugify = this.slugify;
			criteria[slugify('Attendance')]=14;
			criteria[slugify('Homework')]=14;
			criteria[slugify('Quiz')]=14;
			criteria[slugify('Final Exam')]=50;
			criteria[slugify('Teacher\'s Grade')]=8;
			this.criteria = criteria;
			axios.get('/api/seasons').then(response=>{
					this.seasons = response.data;
					this.season = this.seasons[0].id;
					this.loadSections();
				});
			axios.get('/api/score_types').then(response=>{
					this.score_types = response.data;

				});
			
		},
		methods:{
			loadSections(){
				this.sections = [];
				axios.get('api/sections/',{
					params:{
						season_id: this.season
					}
				}).then(response=>{
					this.sections = response.data;
					this.section = this.sections[0].id;
				}).catch(err=>{
					console.error(err);
				});
			},
			submit(){
				this.file = null;
				let data = {
					id: this.section,
					criteria: this.criteria,
					pass_finals:this.pass_finals,
					pass_finals_grade:this.pass_finals_grade,
					passing_grade : this.passing_grade
				}
				axios.post('/api/export',data).then(response=>{
					// console.log(response.data);
					this.file = response.data;
				}).catch(error=>{
					console.error(error);
				});
			},
			slugify(text){
			  return text.toString().toLowerCase()
			    .replace(/\s+/g, '-')           // Replace spaces with -
			    .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
			    .replace(/\-\-+/g, '-')         // Replace multiple - with single -
			    .replace(/^-+/, '')             // Trim - from start of text
			    .replace(/-+$/, '');            // Trim - from end of text
			}
		}
	}
</script>