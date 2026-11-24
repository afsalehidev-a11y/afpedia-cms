using AFPedia.CMS.Infrastructure;

var builder = WebApplication.CreateBuilder(args);

builder.Services.AddCmsInfrastructure(builder.Configuration);

var app = builder.Build();

app.UseAuthentication();
app.UseAuthorization();

app.MapGet("/health", () => Results.Ok(new
{
    status = "ok",
    application = "AFPedia CMS"
}));

app.Run();

public partial class Program;
