<template>
	<div>
		<h3>Attendance</h3>
		<div class="input-group">
			<!-- <span class="input-group-label">New date</span> -->
			<datepicker placeholder="Date" input-class="input-group-field"></datepicker>
			<div class="input-group-button">
				<button class="button primary">Add date</button>
			</div>
		</div>
			<div class="table-scroll">
				<table class="hover">
					<tr>
						<thead>
							<td>Name</td>
							<td v-for="(date,index) in dates">
								{{date.date | moment("MM/DD")}}
							</td>
						</thead>
					</tr>
				</table>
			</div>
		</div>
	</div>
</template>
<script>

export default{
	data(){
		return {
			dates:[]
		}
	},
	mounted(){
		axios.get('/api/sections/'+this.$route.params.id+'/attendance').then(response=>{
			// this.dates = response.data;
			console.log(response.data);
		}).catch(err=>{
			if(err.response){
				console.error(err.response.data.message);
			}else{
				console.error(err);
			}
		});
	}

}
</script>