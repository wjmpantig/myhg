<template>
	<div>
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
			
			<!-- <div class="field">
				<label for="start_date" class="label">Starting date</label>
				<div class="control has-icons-left">
					<datepicker placeholder="Start date" v-bind:input-class="{'input':true,'is-danger': start_date.errors.length>0}" v-model="start_date.date" format="MMM dd yyyy" name="start_date"></datepicker>
					<span class="icon is-left">
						<font-awesome-icon :icon="['far','calendar-alt']"></font-awesome-icon>
					</span>
				</div>
				

			</div> -->
			<!-- <p class="help is-danger" v-show="start_date.errors.length > 0">
				{{start_date.errors.length > 0 ? start_date.errors.join(". ") : ""}}
			</p> -->
			<div class="field">
				<label for="weeks" class="label">No. of weeks</label>
				<div class="control">
					<input type="number" min="1" v-model="weeks" class="input">
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
				seasons:[],
				season:null,
				sections:[],
				section:null,
				weeks: 16,
				file: null,
				// start_date:{
				// 	date: null,
				// 	errors: []
				// }
			}
		},
		computed:{
			fileLink(){
				return this.file == null ? '/#' : '/export/' +this.file.id;
			}
		},
		mounted(){
			this.loadSeasons();
		},
		methods:{
			loadSeasons(){
				axios.get('/api/seasons').then(response=>{
					this.seasons = response.data;
					this.season = this.seasons[0].id;
					this.loadSections();
				});
			},
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
				// let date = this.start_date.date;
				// console.log(date);
				// if(date != null){
				// 	date = this.$moment(date).format('YYYY-MM-DD');
				// }
				// console.log(date);
				let data = {
					id: this.section,
					// start_date: date,
					weeks: this.weeks
				}
				axios.post('/api/export/print',data).then(response=>{
					this.file=response.data;
				}).catch(error=>{
					console.error(error);
				});
			}
		}
	}
</script>
