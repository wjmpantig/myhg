<template>
	<div>
		<form @submit.prevent="addScore">
			<div class="field has-addons">
				<div class="control has-icons-left">
					<datepicker placeholder="New date" v-bind:input-class="{'input':true,'is-danger': new_date.error}" v-model="new_date.date" format="MMM dd yyyy"></datepicker>
					<span class="icon is-left">
						<font-awesome-icon :icon="['far','calendar-alt']"></font-awesome-icon>
					</span>
				</div>
				
				<div class="control">
					<button type="submit" class="button is-primary is-outlined">Add date</button>
				</div>
			</div>
			
		</form>
		<div class="message is-danger" v-show="Object.keys(errors.total).length || Object.keys(errors.students).length">
			<div class="message-header">
				<p>Errors</p>
			</div>
			<div class="message-body">
				<div v-for="(error_date,index) in errors.total" >
					<p class="has-text-weight-semibold">{{scores[index].date}}</p>
					<ul  v-for="field_errors,name in error_date">
						<li v-for="error in field_errors">
							{{error}}
						</li>
					</ul>
				</div>
				<div v-for="(student,index) in errors.students" >
					<p class="has-text-weight-semibold">{{students[index].last_name}}, {{students[index].first_name}}</p>
					<ul  v-for="date in student">
						<li v-for="errors in date.errors">
							<div v-for="error in errors">
								{{date.date}} : {{error}}
																
							</div>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="table-wrapper">
			<table v-bind:class="{'table is-hoverable is-bordered is-striped': true,'is-fullwidth is-narrow':scores.length > 5}">				
				<thead>
					<tr>
						<th>Total</th>
						<th v-for="(score,index) in scores" class="has-text-centered">
							<input type="text" v-bind:class="{input:true,'is-danger':errors.total[score.id]}" v-model="score.total" v-on:blur="updateTotal(score,index)" :disabled="score.isUpdating">
						</th>
					</tr>
					<tr>
						<th>Name</th>
						<th v-for="(score,index) in scores" class="has-text-centered">
							<div>{{score.date | moment("MM/DD")}}</div>
							<a @click="confirmDelete(score)" class="has-text-danger">
								<font-awesome-icon :icon="['fas','times']"></font-awesome-icon>
							</a>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(student,index) in students">
						<td>{{ student.last_name}}, {{ student.first_name}}</td>
						<td v-for="(score) in scores" class="has-text-centered">
							<input v-bind:class="{input:true, 'is-danger':errors.students[index] && errors.students[index][score.id]}" type="text" v-model="student.scores[score.id]" min="0" v-bind:max="score.total" v-on:blur="updateScore(score,student,index)" 
							:disabled="student.update_status && student.update_status[index] && student.update_status[index][score.id]">
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>
<script>
	export default{
		data(){
			return {
				score_type:{},
				scores:[],
				students: [],
				errors:{
					total:{},
					students:{}
				},
				new_date:{
					date:null,
					error:null
				}
			};
		},
		mounted(){
			axios.get('/api/score_types/' + this.$route.meta.type_id).then(response=>{
				this.score_type = response.data;
			}).catch(err=>{
				if(err.response){
					console.error(err.response.data.message);
				}else{
					console.error(err);
				}
			});
			this.loadScores();
		},
		methods:{
			loadScores(){
				console.log();
				axios.get('/api/sections/'+this.$route.params.id+'/scores/'+this.$route.meta.type_id).then(response=>{
				this.scores = response.data.scores;
				this.students = response.data.students;
				}).catch(err=>{
					if(err.response){
						console.error(err.response.data.message);
					}else{
						console.error(err);
					}
				});
			
			},
			updateTotal(score,index){
				// console.log(score);
				// this.errors.total.splice(index,1);
				Vue.delete(this.errors.total,index);

				Vue.set(score,'isUpdating',true);
				if(score.total.length ==0){
					score.total = 0;
				}
				axios.post('/api/sections/'+this.$route.params.id+'/scores/'+this.$route.meta.type_id+'/'+score.id,score).then(response=>{
					Vue.set(this.scores,index,response.data);
					// Vue.set(this.errors,index,null);
				}).catch(err=>{
					
					if(err.response){
						console.error(err.response.data.message);
						Vue.set(this.errors.total,index,err.response.data.errors)

					}else{
						console.error(err);
						Vue.set(this.errors.total,index,err);
					}
				}).then(()=>{
					Vue.set(score,'isUpdating',false);
				});
			},
			updateScore(score,student,index){
				// console.log('update called',score,student);
				if(this.errors.students[index]){
					Vue.delete(this.errors.students[index],score.id);
				}
				let student_score = student.scores[score.id];
				console.log(student_score);
				if(!student.update_status){

					Vue.set(student,'update_status',{});
				}
				if(!student.update_status[index]){
					Vue.set(student.update_status,index,{});	
				}
				Vue.set(student.update_status[index],score.id,true);
				axios.post('/api/sections/'+this.$route.params.id+'/scores/'+this.$route.meta.type_id+'/'+score.id + '/' + student.id,{
					score:student_score}).then(response=>{
					// Vue.set(this.scores,index,response.data);
					// Vue.set(this.errors,index,null);
					Vue.set(student.scores,score.id,response.data.score);
					// console.log(response)
				}).catch(err=>{
					if(!this.errors.students[index]){
						Vue.set(this.errors.students,index,{});
					}
					if(err.response){
						console.error(err.response.data.message);
						Vue.set(this.errors.students[index],score.id,{
							date: score.date,
							errors:err.response.data.errors
						});
						

					}else{
						console.error(err);
						Vue.set(this.errors.students[index],score.id,{
							date: score.date,
							errors:err
						});
					}
				}).then(()=>{
					Vue.set(student.update_status[index],score.id,false);
				});

			},
			confirmDelete(date){

				this.$dialog.confirm('Confirm delete date:\"'+this.$moment(date.date).format('MMM DD, YYYY')+'\"?',{
					loader:true
				}).then((dialog)=>{
					this.deleteDate(date.id,dialog);
				});
			},
			deleteDate(section_score_id,dialog){

				axios.delete('/api/sections/'+this.$route.params.id+'/scores/'+this.$route.meta.type_id+'/' +section_score_id).then(response=>{
					// console.log(response.data)
					this.loadScores();
					dialog.close()
				}).catch(err=>{
					dialog.close();
					let error = err.response.data;
					console.error(error);
					let message = error.message ? error.message : error;
					this.$dialog.alert('Error: ' + message);
				});
			},
			addScore(){
				this.new_date.error = null;
				let date = this.new_date.date;
				// console.log(this.new_date);
				if(date != null){
					date = this.$moment(date).format('YYYY-MM-DD');
				}
				console.log(date);
				axios.put('/api/sections/'+this.$route.params.id+'/scores/'+this.$route.meta.type_id,{
					date
				}).then(response=>{
					// console.log(response.data)
					this.loadScores();
				}).catch(err=>{
					if(err.response){
						console.error(err.response.data.message);
						this.new_date.error = err.response.data.errors['date'];
					}else{
						console.error(err);
					}
				});
			}

		}
	}

</script>