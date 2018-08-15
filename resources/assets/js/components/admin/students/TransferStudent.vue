<template>
	<div>
		<h2 class="title">Transfer student</h2>

		<div class="subtitle">{{user.last_name}} {{user.first_name}}</div>
		<div class="columns">
			<div class="column is-narrow">
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
			<div class="column is-narrow">
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
			<div class="column is-narrow" v-show="from_section.id != null && target_section.id != null">
				<button class="button is-danger" @click="confirmSubmit()">Transfer</button>
			</div>
		</div>
		<div class="tabs-wrapper" v-show="from_section.id != null && target_section.id != null">
			<div class="tabs">
				<ul>
					<li v-bind:class="{'is-active':selected_tab==0}"><a @click="selected_tab = 0">Attendance</a></li>
					<li v-bind:class="{'is-active':selected_tab==1}"><a @click="selected_tab = 1">Homeworks</a></li>
					<li v-bind:class="{'is-active':selected_tab==2}"><a @click="selected_tab = 2">Quizzes</a></li>
					<li v-bind:class="{'is-active':selected_tab==3}"><a @click="selected_tab = 3">Final Exam</a></li>
				</ul>
			</div>
			<div class="columns tab-contents" v-show="selected_tab == 0">
				<div class="column is-narrow">

					<table class="table is-hoverable is-striped is-bordered">
						<thead>
							<tr>
								<th colspan="2" class="has-text-centered">{{from_section.name}}</th>
							</tr>
							<tr>
								<th>Date</th>
								<th>Present</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="date in current_data.dates">
								<td>{{ date.date | moment("MM/DD") }}</td>
								<td class="has-text-centered"><input type="checkbox" 
									:checked="current_data.attendance[date.id] && current_data.attendance[date.id].is_present" disabled="disabled"></td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="column is-narrow">
					<table class="table is-hoverable is-striped is-bordered">
						<thead>
							<tr>
								<th colspan="2" class="has-text-centered">{{target_section.name}}</th>
							</tr>
							<tr>
								<th>Date</th>
								<th>Present</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="date in target_data.dates">
								<td>{{ date.date | moment("MM/DD") }}</td>
								<td class="has-text-centered">
									<input type="checkbox" 
									:checked="target_data.attendance[date.id] && target_data.attendance[date.id].is_present" 
									@change="updateAttendance(date.id)">
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>	
			<div class="columns tab-contents" v-for="total,type in current_data.totals" v-show="selected_tab == type">
				<div class="column is-narrow">
					<table class="table is-hoverable is-striped is-bordered">
						<thead>
							<tr>
								<th colspan="3" class="has-text-centered">{{from_section.name}}</th>
							</tr>
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
								<td>{{current_data.scores[id] ? current_data.scores[id].score : ''}}</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="column">
					<table class="table is-hoverable is-striped is-bordered">
						<thead>
							<tr>
								<th colspan="3" class="has-text-centered">{{target_section.name}}</th>
							</tr>
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
								<td>
									<input type="number"
									min="0" :max="section_score.total"
									:value="target_data.scores[id] ? target_data.scores[id].score : null" 
									:ref="'target_score_'+id" 
									:class="{'input':true,'is-danger':target_data.scores[id] && !validateScore(id,type)}"
									@blur="updateScore(id)">
								</td>
							</tr>
						</tbody>
					</table>
				</div>
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
					attendance: {},
					totals:{},
					scores:{}

				},
				current_data:{
					dates:[],
					attendance: {},
					totals:{},
					scores:{}
				},
				selected_tab: 0,
				errors:[]
			};
		},
		mounted(){
			axios.get('/api/students/'+this.$route.params.id).then(response=>{
				this.user = response.data;
			});
		},
		methods:{
			updateAttendance(date_id){
				if(!this.target_data.attendance[date_id]){
					Vue.set(this.target_data.attendance,date_id,{
						id: date_id,
						is_present: true
					});
					return;
				}
				this.target_data.attendance[date_id].is_present = !this.target_data.attendance[date_id].is_present;
			},
			updateScore(id){
				
				let score = this.$refs['target_score_'+id][0].value;
				// score = Number.parseFloat(score);
				if(!this.target_data.scores[id]){
					Vue.set(this.target_data.scores,id,{
						id,
						score
					});
					return;
				}
				this.target_data.scores[id].score = score;
			},
			validateScore(id,type){
				if(!this.target_data.scores[id]){
					return true;
				}
				// console.log(this.target_data.scores[id]);	
				let score = this.target_data.scores[id].score.trim();
				// console.log('score value is '+score)
				if(score.length == 0){
					// console.log('empty');
					return true;
				}
				let total = this.target_data.totals[type][id].total;
				// console.log(total)
				if(isNaN(score) && !Number.isFinite(score)){
					// console.log('not finite')
					return false;
				}
				score = parseFloat(score);
				if(score <0 || score >total){
					return false;
				}
				return true;
			},
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
				if(for_target){
					this.target_data.dates = [];
					this.target_data.attendance = {};
				}else{
					this.current_data.dates = [];
					this.current_data.attendance = {};
				}
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
				if(for_target){
					this.target_data.totals = {};
					this.target_data.scores = {};
				}else{
					this.current_data.totals = {};
					this.current_data.scores = {};
				}
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
			},
			confirmSubmit(){
				let user = this.user;
				let message = '<div>Confirm transfer of <span class="has-text-weight-semibold">'+user.last_name +', '+user.first_name +'</span></div>'
					+ '<div>From: <span class="has-text-weight-semibold">' + this.from_section.name + '</span></div>' 
					+ '<div>To: <span class="has-text-weight-semibold">' + this.target_section.name + '</span></div>' ;
				this.$dialog.confirm(message,{
					loader:true,
					html:true
				}).then((dialog)=>{
					this.transferStudent(dialog);
				});
			},
			transferStudent(dialog){
				let data = {
					attendance: this.target_data.attendance,
					scores: this.target_data.scores
				}
				axios.post('/api/students/'+this.user.id+'/transfer',{
					from_section_id:this.from_section.id,
					target_section_id: this.target_section.id,
					data
				}).then(response=>{
					// console.log(response.data)
					
					dialog.close()
				}).catch(err=>{
					dialog.close();
					let error = err.response.data;
					console.error(error);
					let message = error.message ? error.message : error;
					this.$dialog.alert('Error: ' + message);
				});
			},
		}

	}
</script>