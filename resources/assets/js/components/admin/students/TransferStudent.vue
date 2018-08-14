<template>
	<div>
		<h2 class="title">Transfer student</h2>
		<div class="subtitle">{{user.last_name}} {{user.first_name}}</div>
		<div class="columns">
			<div class="column">
				<div class="field">
					<label for="" class="label">Transfer from:</label>
					<div class="control">
						<div class="select">
							<select v-model="from_section" @change="loadSections() | loadAttendance(from_section.id,false) | loadScores(from_section.id,false)">
								<option v-for="section in user.sections" :value="section">{{section.season_name}} season - {{section.name}}</option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="column">
				<div class="field" v-show="target_sections.length > 0">
					<label for="" class="label">Transfer to:</label>
					<div class="control">
						<div class="select">
							<select v-model="target_section" @change="loadAttendance(target_section.id,true) | loadScores(target_section.id,true)">
								<option v-for="section in target_sections" :value="section">{{section.name}}</option>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>	
		
		<h4 class="title is-4">Attendance</h4>		
		<div class="columns">
			<div class="column">
				<div>Section: {{from_section.name}}</div>
				<table class="table is-hoverable">
					<thead>
						<tr>
							<th>Date</th>
							<th>Present</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="date in current_data.dates">
							<td>{{ date.date | moment("MM/DD") }}</td>
							<td class="has-text-centered"><input type="checkbox" v-model="current_data.attendance[date.id]" disabled="disabled"></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="column">
				<div>Section: {{target_section.name}}</div>
				
				<table class="table is-hoverable">
					<thead>
						<tr>
							<th>Date</th>
							<th>Present</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="date in target_data.dates">
							<td>{{ date.date | moment("MM/DD") }}</td>
							<td class="has-text-centered"><input type="checkbox" v-model="target_data.attendance[date.id+'']"></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<h4 class="title is-4">Scores</h4>
		<div class="columns" v-for="total,type in current_data.totals">
			<div class="column">
				<table class="table is-hoverable">
					<thead>
						<tr>
							<th>Date</th>
							<th>Total</th>
							<th>Score</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="section_score,id in total">
							<td>{{section_score.date}}</td>
							<td>{{section_score.total}}</td>
							<td>{{current_data.scores[id]}}</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="column">
				<table class="table is-hoverable">
					<thead>
						<tr>
							<th>Date</th>
							<th>Total</th>
							<th>Score</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="section_score,id in target_data.totals[type]">
							<td>{{section_score.date}}</td>
							<td>{{section_score.total}}</td>
							<td><input type="text" class="input" v-model="target_data.scores[id+'']"></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</template>
<script>
	export default{
		data(){
			return {
				user:{},
				from_section:{},
				target_sections: [],
				target_section:{},
				target_data:{
					dates:[],
					attendance: [],
					totals:[],
					scores:[]

				},
				current_data:{
					dates:[],
					attendance: [],
					totals:[],
					scores:[]
				},
				
				
			};
		},
		mounted(){
			axios.get('/api/students/'+this.$route.params.id).then(response=>{
				this.user = response.data;
			});
		},
		methods:{
			loadSections(){
				axios.get('api/sections/',{
					params: {
						season_id:this.season,
						except: this.from_section.id
					}
				}).then(response=>{
					this.target_sections = response.data;
				}).catch(err=>{
					console.error(err);
				});
			},
			loadAttendance(section_id,for_target){
				axios.get('api/students/'+this.user.id+'/attendance/'+section_id).then(response=>{
					if(for_target){
						this.target_data.dates = response.data.dates;
						this.target_data.attendance = response.data.attendance;
					}else{
						this.current_data.dates = response.data.dates;
						this.current_data.attendance = response.data.attendance;
					}
				}).catch(err=>{
					console.error(err);
				})
			},
			loadScores(section_id,for_target){
				axios.get('api/students/'+this.user.id+'/scores/'+section_id).then(response=>{
					if(for_target){
						this.target_data.totals = response.data.totals;
						this.target_data.scores = response.data.scores;
					}else{
						this.current_data.totals = response.data.totals;
						this.current_data.scores = response.data.scores;
					}
					
				}).catch(err=>{
					console.error(err);
				});
			}
		}

	}
</script>